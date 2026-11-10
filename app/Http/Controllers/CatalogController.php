<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'condition' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:100'],
        ]);

        $products = Product::query()
            ->purchasable()
            ->with(['category', 'seller'])
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $query->where(function ($search) use ($filters): void {
                    $search->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('description', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when(filled($filters['category'] ?? null), fn ($query) => $query->where('category_id', $filters['category']))
            ->when(filled($filters['min_price'] ?? null), fn ($query) => $query->where('price', '>=', $filters['min_price']))
            ->when(filled($filters['max_price'] ?? null), fn ($query) => $query->where('price', '<=', $filters['max_price']))
            ->when(filled($filters['condition'] ?? null), fn ($query) => $query->where('condition', $filters['condition']))
            ->when(filled($filters['location'] ?? null), fn ($query) => $query->where('location', 'like', '%'.$filters['location'].'%'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', [
            'products' => $products,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'conditions' => SellerProductController::CONDITIONS,
            'filters' => $filters,
        ]);
    }

    public function show(Product $product): View
    {
        $isOwner = auth()->check() && auth()->id() === $product->user_id;
        abort_unless($product->status === 'active' || $isOwner, 404);

        $product->load(['category', 'seller', 'reviews.buyer']);

        return view('catalog.show', [
            'product' => $product,
            'isOwner' => $isOwner,
            'reviewCount' => $product->reviews->count(),
            'avgRating' => round((float) $product->reviews->avg('rating'), 1),
            'sellerRating' => round((float) $product->seller?->reviewsReceived()->avg('rating'), 1),
            'sellerReviewCount' => $product->seller?->reviewsReceived()->count() ?? 0,
        ]);
    }
}
