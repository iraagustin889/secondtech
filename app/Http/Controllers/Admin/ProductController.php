<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
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

    public function hide(Request $request, Product $product)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Alasan moderasi wajib diisi.',
            'reason.min' => 'Alasan minimal 5 karakter.',
        ]);

        $product->update(['status' => 'hidden']);

        AdminLog::record('hide_product', 'product', $product->id, $data['reason']);

        return back()->with('status', "Produk {$product->name} disembunyikan.");
    }

    public function activate(Product $product)
    {
        $product->update(['status' => 'active']);

        AdminLog::record('activate_product', 'product', $product->id);

        return back()->with('status', "Produk {$product->name} diaktifkan kembali.");
    }
}