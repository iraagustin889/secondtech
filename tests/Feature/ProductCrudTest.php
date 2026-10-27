<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Matikan Vite supaya test tidak butuh npm run build
        $this->withoutVite();

        $this->category = Category::create([
            'name' => 'Laptop',
            'slug' => 'laptop',
            'is_active' => true,
        ]);
    }

    private function makeUser(): User
    {
        return User::factory()->create(['is_active' => true]);
    }

    private function makeProduct(User $owner, array $override = []): Product
    {
        return Product::factory()->create(array_merge([
            'user_id' => $owner->id,
            'category_id' => $this->category->id,
        ], $override));
    }

    private function validPayload(array $override = []): array
    {
        return array_merge([
            'name' => 'Laptop Bekas',
            'description' => 'Masih mulus',
            'price' => 2500000,
            'condition' => 'Bekas baik',
            'weight' => 2000,
            'location' => 'Banyuwangi',
            'stock' => 1,
            'category_id' => $this->category->id,
        ], $override);
    }

    public function test_guest_cannot_open_product_list(): void
    {
        $this->get(route('seller.products.index'))->assertRedirect();
    }

    public function test_user_sees_only_own_products(): void
    {
        $me = $this->makeUser();
        $other = $this->makeUser();
        $this->makeProduct($me, ['name' => 'Produk Saya Aja']);
        $this->makeProduct($other, ['name' => 'Produk Orang Lain']);

        $this->actingAs($me)
            ->get(route('seller.products.index'))
            ->assertOk()
            ->assertSee('Produk Saya Aja')
            ->assertDontSee('Produk Orang Lain');
    }

    public function test_user_can_create_product(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('seller.products.store'), $this->validPayload())
            ->assertRedirect(route('seller.products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Laptop Bekas',
            'user_id' => $user->id,
        ]);
    }

    public function test_create_product_can_upload_photo(): void
    {
        Storage::fake('public');
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('seller.products.store'), $this->validPayload([
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ]));

        $product = Product::first();
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_create_product_validation_rejects_bad_input(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('seller.products.store'), $this->validPayload([
                'name' => '',
                'price' => 500,
            ]))
            ->assertSessionHasErrors(['name', 'price']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_inactive_category_cannot_be_chosen(): void
    {
        $user = $this->makeUser();
        $this->category->update(['is_active' => false]);

        $this->actingAs($user)
            ->post(route('seller.products.store'), $this->validPayload())
            ->assertSessionHasErrors('category_id');
    }

    public function test_owner_can_update_product(): void
    {
        $owner = $this->makeUser();
        $product = $this->makeProduct($owner);

        $this->actingAs($owner)
            ->put(route('seller.products.update', $product), $this->validPayload(['name' => 'Nama Baru']))
            ->assertRedirect(route('seller.products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Nama Baru']);
    }

    public function test_other_user_gets_403_on_edit_update_toggle_and_delete(): void
    {
        $owner = $this->makeUser();
        $intruder = $this->makeUser();
        $product = $this->makeProduct($owner);

        $this->actingAs($intruder);

        $this->get(route('seller.products.edit', $product))->assertForbidden();
        $this->put(route('seller.products.update', $product), $this->validPayload())->assertForbidden();
        $this->patch(route('seller.products.toggle', $product))->assertForbidden();
        $this->delete(route('seller.products.destroy', $product))->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'active']);
    }

    public function test_owner_can_hide_and_show_product(): void
    {
        $owner = $this->makeUser();
        $product = $this->makeProduct($owner);

        $this->actingAs($owner)->patch(route('seller.products.toggle', $product));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'hidden']);

        $this->patch(route('seller.products.toggle', $product));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'active']);
    }

    public function test_owner_can_delete_unordered_product(): void
    {
        $owner = $this->makeUser();
        $product = $this->makeProduct($owner);

        $this->actingAs($owner)
            ->delete(route('seller.products.destroy', $product))
            ->assertRedirect(route('seller.products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_ordered_product_cannot_be_deleted(): void
    {
        $owner = $this->makeUser();
        $product = $this->makeProduct($owner);

        OrderItem::create([
            'order_id' => Order::factory()->create()->id,
            'product_id' => $product->id,
            'price_snapshot' => 100000,
            'qty' => 1,
        ]);

        $this->actingAs($owner)
            ->delete(route('seller.products.destroy', $product))
            ->assertRedirect(route('seller.products.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
