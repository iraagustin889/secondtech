<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Menampilkan semua kategori beserta jumlah produknya
    public function index()
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    // Menyimpan kategori baru
    public function store(Request $request)
    {
        // Buat slug dari nama, lalu ikut divalidasi supaya tidak ada slug kembar
        // (contoh: "Audio & Headphone" dan "Audio Headphone" menghasilkan slug sama)
        $request->merge(['slug' => Str::slug($request->input('name', ''))]);

        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'slug' => ['required', 'unique:categories,slug'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori sudah ada.',
            'slug.unique'   => 'Kategori dengan nama serupa sudah ada.',
        ]);

        Category::create([
            'name'      => $request->name,
            'slug'      => $request->slug,
            'is_active' => true, // kategori baru langsung aktif
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    // Mengubah status aktif <-> nonaktif
    public function toggle(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori {$category->name} berhasil {$status}.");
    }
}
