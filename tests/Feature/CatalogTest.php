<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private Category $laptop;

    private Category $phone;

    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seller = User::factory()->create();
        $this->laptop = Category::create(['name' => 'Laptop', 'slug' => 'laptop', 'is_active' => true]);
        $this->phone = Category::create(['name' => 'Smartphone', 'slug' => 'smartphone', 'is_active' => true]);
    }

    private function makeProduct(array $attributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'user_id' => $this->seller->id,
            'category_id' => $this->laptop->id,
            'name' => 'Produk Contoh',
            'price' => 1000000,
            'condition' => 'Bekas baik',
            'location' => 'Banyuwangi',
            'stock' => 1,
            'status' => 'active',
        ], $attributes));
    }

    public function test_guest_can_open_catalog(): void
    {
        $this->get(route('products.index'))->assertOk();
    }

    public function test_only_active_products_with_stock_are_shown(): void
    {
        $this->makeProduct(['name' => 'Tampil Aktif']);
        $this->makeProduct(['name' => 'Disembunyikan', 'status' => 'hidden']);
        $this->makeProduct(['name' => 'Sudah Terjual', 'status' => 'sold']);
        $this->makeProduct(['name' => 'Stok Habis', 'stock' => 0]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Tampil Aktif')
            ->assertDontSee('Disembunyikan')
            ->assertDontSee('Sudah Terjual')
            ->assertDontSee('Stok Habis');
    }

    public function test_search_by_name_is_case_insensitive(): void
    {
        $this->makeProduct(['name' => 'Laptop Asus ROG']);
        $this->makeProduct(['name' => 'Kamera Canon']);

        $this->get(route('products.index', ['q' => 'asus']))
            ->assertSee('Laptop Asus ROG')
            ->assertDontSee('Kamera Canon');
    }

    public function test_filter_by_category(): void
    {
        $this->makeProduct(['name' => 'Laptop A', 'category_id' => $this->laptop->id]);
        $this->makeProduct(['name' => 'Ponsel B', 'category_id' => $this->phone->id]);

        $this->get(route('products.index', ['category' => $this->phone->id]))
            ->assertSee('Ponsel B')
            ->assertDontSee('Laptop A');
    }

    public function test_filter_by_price_range(): void
    {
        $this->makeProduct(['name' => 'Murah', 'price' => 500000]);
        $this->makeProduct(['name' => 'Sedang', 'price' => 2000000]);
        $this->makeProduct(['name' => 'Mahal', 'price' => 9000000]);

        $this->get(route('products.index', ['min_price' => 1000000, 'max_price' => 5000000]))
            ->assertSee('Sedang')
            ->assertDontSee('Murah')
            ->assertDontSee('Mahal');
    }

    public function test_filter_by_condition(): void
    {
        $this->makeProduct(['name' => 'Kondisi Baru', 'condition' => 'Seperti baru']);
        $this->makeProduct(['name' => 'Kondisi Rusak', 'condition' => 'Butuh perbaikan']);

        $this->get(route('products.index', ['condition' => 'Seperti baru']))
            ->assertSee('Kondisi Baru')
            ->assertDontSee('Kondisi Rusak');
    }

    public function test_filter_by_location(): void
    {
        $this->makeProduct(['name' => 'Di Banyuwangi', 'location' => 'Banyuwangi']);
        $this->makeProduct(['name' => 'Di Surabaya', 'location' => 'Surabaya']);

        $this->get(route('products.index', ['location' => 'surabaya']))
            ->assertSee('Di Surabaya')
            ->assertDontSee('Di Banyuwangi');
    }

    public function test_combined_filters(): void
    {
        $this->makeProduct([
            'name' => 'Target Laptop', 'category_id' => $this->laptop->id,
            'price' => 3000000, 'condition' => 'Bekas baik', 'location' => 'Surabaya',
        ]);
        $this->makeProduct([
            'name' => 'Salah Kategori', 'category_id' => $this->phone->id,
            'price' => 3000000, 'condition' => 'Bekas baik', 'location' => 'Surabaya',
        ]);
        $this->makeProduct([
            'name' => 'Salah Harga', 'category_id' => $this->laptop->id,
            'price' => 9000000, 'condition' => 'Bekas baik', 'location' => 'Surabaya',
        ]);
        $this->makeProduct([
            'name' => 'Salah Lokasi', 'category_id' => $this->laptop->id,
            'price' => 3000000, 'condition' => 'Bekas baik', 'location' => 'Malang',
        ]);

        $this->get(route('products.index', [
            'q' => 'laptop',
            'category' => $this->laptop->id,
            'min_price' => 1000000,
            'max_price' => 5000000,
            'condition' => 'Bekas baik',
            'location' => 'Surabaya',
        ]))
            ->assertSee('Target Laptop')
            ->assertDontSee('Salah Kategori')
            ->assertDontSee('Salah Harga')
            ->assertDontSee('Salah Lokasi');
    }

    public function test_shows_message_when_no_product_found(): void
    {
        $this->makeProduct(['name' => 'Laptop Biasa']);

        $this->get(route('products.index', ['q' => 'tidak-ada-produk-ini']))
            ->assertOk()
            ->assertSee('Produk tidak ditemukan');
    }

    public function test_pagination_keeps_filters(): void
    {
        for ($i = 1; $i <= 13; $i++) {
            $this->makeProduct(['name' => "Laptop Nomor {$i}"]);
        }

        $this->get(route('products.index', ['q' => 'Laptop']))
            ->assertOk()
            ->assertSee('page=2')
            ->assertSee('q=Laptop');
    }
}
