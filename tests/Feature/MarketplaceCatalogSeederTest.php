<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MarketplaceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_presentable_idempotent_demo_catalog_and_hides_legacy_faker_listings(): void
    {
        $this->seed(CategorySeeder::class);

        $legacySeller = User::factory()->create(['email' => 'legacy@example.test']);
        $legacyProduct = Product::factory()
            ->for($legacySeller, 'seller')
            ->for(Category::query()->firstOrFail())
            ->create(['name' => 'provident minima nobis', 'status' => 'active']);

        $this->seed(MarketplaceCatalogSeeder::class);

        $this->assertDatabaseHas('products', [
            'name' => 'MacBook Air M1 2020 8GB/256GB',
            'price' => 7_800_000,
            'condition' => 'Bekas baik',
            'location' => 'Banyuwangi',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('products', ['id' => $legacyProduct->id, 'status' => 'hidden']);
        $this->assertDatabaseCount('products', 12);

        $this->seed(MarketplaceCatalogSeeder::class);

        $this->assertDatabaseCount('products', 12);
    }
}
