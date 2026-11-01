<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q'         => ['nullable', 'string', 'max:100'],
            'category'  => ['nullable', 'integer'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'condition' => ['nullable', 'string', 'max:50'],
            'location'  => ['nullable', 'string', 'max:100'],
        ]);

        $products = Product::query()
            ->with('category')
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['min_price'] ?? null, fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($filters['max_price'] ?? null, fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($filters['condition'] ?? null, fn ($q, $v) => $q->where('condition', $v))
            ->when($filters['location'] ?? null, fn ($q, $v) => $q->where('location', 'like', "%{$v}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', [
            'products'   => $products,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'conditions' => SellerProductController::CONDITIONS,
            'filters'    => $filters,
        ]);
    }
    public function show(Product $product)
    {
        // Produk tidak aktif hanya boleh dilihat pemiliknya.
        $isOwner = auth()->check() && auth()->id() === $product->user_id;
        abort_unless($product->status === 'active' || $isOwner, 404);

        $product->load(['category', 'seller']);

        return view('catalog.show', [
            'product' => $product,
            'isOwner' => $isOwner,
        ]);
    }
}
