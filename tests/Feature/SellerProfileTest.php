<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_seller_profile_shows_bio_rating_and_active_products(): void
    {
        $this->withoutVite();
        $seller = User::factory()->create([
            'name' => 'Toko Akbar',
            'location' => 'Banyuwangi',
            'seller_bio' => 'Menjual laptop dan ponsel bekas yang sudah dicek.',
        ]);
        $product = Product::factory()->for($seller, 'seller')->for(Category::factory())->create([
            'name' => 'Laptop siap pakai',
            'status' => 'active',
            'stock' => 2,
        ]);
        Product::factory()->for($seller, 'seller')->for(Category::factory())->create(['status' => 'hidden']);

        $this->get(route('sellers.show', $seller))
            ->assertOk()
            ->assertSee('Toko Akbar')
            ->assertSee('Menjual laptop dan ponsel')
            ->assertSee('Laptop siap pakai')
            ->assertDontSee('Produk Tersembunyi');

        $this->assertSame(2, $product->stock);
    }
}
