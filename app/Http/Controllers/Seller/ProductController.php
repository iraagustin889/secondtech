<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    // Menampilkan daftar produk milik penjual yang sedang login
    public function index()
    {
        // Hanya produk milik user ini (lewat relasi products() di model User),
        // diurutkan dari yang terbaru, dengan data kategori ikut diambil
        $products = auth()->user()
            ->products()
            ->with('category')
            ->latest()
            ->get();

        return view('seller.products.index', compact('products'));
    }
}