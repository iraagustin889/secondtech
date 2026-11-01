<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $products = Product::query()
            ->with(['seller', 'category'])
            ->when($search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search', 'status'));
    }

    public function show(Product $product)
    {
        $product->load(['seller', 'category']);

        return view('admin.products.show', compact('product'));
    }
}