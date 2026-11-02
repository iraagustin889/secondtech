<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_only_displays_purchasable_products_and_applies_keyword_filter(): void
    {
        $seller = User::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->for($seller, 'seller')->for($category)->create(['name' => 'Laptop Gaming', 'status' => 'active', 'stock' => 1]);
        Product::factory()->for($seller, 'seller')->for($category)->create(['name' => 'Laptop Tersembunyi', 'status' => 'hidden']);
        Product::factory()->for($seller, 'seller')->for($category)->create(['name' => 'Laptop Habis', 'status' => 'active', 'stock' => 0]);

        $this->get(route('catalog.index', ['q' => 'Gaming']))
            ->assertOk()
            ->assertSee('Laptop Gaming')
            ->assertDontSee('Laptop Tersembunyi')
            ->assertDontSee('Laptop Habis');
    }
}
