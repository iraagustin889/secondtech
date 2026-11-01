<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->seller = User::factory()->create(['is_active' => true]);
        $this->category = Category::create(['name' => 'Laptop', 'slug' => 'laptop', 'is_active' => true]);
    }

    private function makeProduct(array $attributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'user_id'     => $this->seller->id,
            'category_id' => $this->category->id,
            'name'        => 'Laptop Detail',
            'description' => 'Deskripsi lengkap laptop',
            'price'       => 4500000,
            'condition'   => 'Bekas baik',
            'weight'      => 1800,
            'location'    => 'Surabaya',
            'stock'       => 1,
            'status'      => 'active',
        ], $attributes));
    }

    public function test_guest_can_view_active_product_detail(): void
    {
        $product = $this->makeProduct();

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Laptop Detail')
            ->assertSee('4.500.000')
            ->assertSee('Laptop')
            ->assertSee('Bekas baik')
            ->assertSee('Deskripsi lengkap laptop')
            ->assertSee('1.800 gram')
            ->assertSee('Surabaya')
            ->assertSee($this->seller->name);
    }

    public function test_hidden_product_is_not_found_for_guest_and_other_users(): void
    {
        $product = $this->makeProduct(['status' => 'hidden']);

        $this->get(route('products.show', $product))->assertNotFound();

        $this->actingAs(User::factory()->create(['is_active' => true]))
            ->get(route('products.show', $product))
            ->assertNotFound();
    }

    public function test_owner_can_view_own_hidden_product(): void
    {
        $product = $this->makeProduct(['status' => 'hidden']);

        $this->actingAs($this->seller)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Hanya Anda yang bisa melihat');
    }

    public function test_buy_button_is_hidden_for_own_product(): void
    {
        $product = $this->makeProduct();

        $this->actingAs($this->seller)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Ini produk Anda')
            ->assertDontSee('id="buy-button"', false);
    }

    public function test_buy_button_is_shown_for_other_users(): void
    {
        $product = $this->makeProduct();

        $this->actingAs(User::factory()->create(['is_active' => true]))
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('id="buy-button"', false)
            ->assertDontSee('Ini produk Anda');
    }

    public function test_catalog_card_links_to_detail(): void
    {
        $product = $this->makeProduct();

        $this->get(route('products.index'))
            ->assertSee(route('products.show', $product), false);
    }
}
