<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // Daftar kondisi barang yang boleh dipilih (dipakai di form dan validasi)
    private const CONDITIONS = ['Seperti baru', 'Bekas baik', 'Bekas normal', 'Butuh perbaikan'];

    // Menampilkan daftar produk milik user yang sedang login
    public function index()
    {
        $products = auth()->user()
            ->products()
            ->with('category')
            ->latest()
            ->get();

        return view('seller.products.index', compact('products'));
    }

    // Menampilkan form tambah produk
    public function create()
    {
        // Dropdown hanya berisi kategori AKTIF,
        // jadi kategori nonaktif otomatis tidak bisa dipilih
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('seller.products.create', [
            'categories' => $categories,
            'conditions' => self::CONDITIONS,
        ]);
    }

    // Menyimpan produk baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1000'],
            'condition' => ['required', Rule::in(self::CONDITIONS)],
            'weight' => ['required', 'integer', 'min:1'],
            'location' => ['required', 'string', 'max:100'],
            'stock' => ['required', 'integer', 'min:1'],
            // Kategori harus ada DAN aktif. Ini pagar kedua kalau
            // seseorang mengakali form dan mengirim kategori nonaktif.
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            // BARU: foto opsional, harus gambar jpg/png/webp, maksimal 2 MB
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka bulat.',
            'price.min' => 'Harga minimal Rp 1.000.',
            'condition.required' => 'Kondisi wajib dipilih.',
            'condition.in' => 'Kondisi tidak valid.',
            'weight.required' => 'Berat wajib diisi.',
            'weight.min' => 'Berat minimal 1 gram.',
            'location.required' => 'Lokasi wajib diisi.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.min' => 'Stok minimal 1.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid atau sudah nonaktif.',
            // BARU: pesan error foto
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format foto harus jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        // BARU: simpan file ke storage/app/public/products,
        // lalu yang disimpan di database hanya path-nya (teks), bukan file-nya
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($data['image']); // tidak ada foto, kolom image tetap null
        }

        // Pemilik produk selalu user yang login (bukan dari input form)
        $data['user_id'] = auth()->id();

        \App\Models\Product::create($data);

        return redirect()
            ->route('seller.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }
}
