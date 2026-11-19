<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_open_the_business_dashboard(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        Product::factory()->for($seller, 'seller')->for(Category::factory())->create(['status' => 'active']);

        $this->actingAs($seller)
            ->get(route('seller.dashboard'))
            ->assertOk()
            ->assertViewIs('seller.dashboard')
            ->assertSee('Overview Bisnis')
            ->assertSee('Produk aktif');
    }
}
