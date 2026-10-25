<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        return view('seller.products.create', [
            'categories' => $this->activeCategories(),
            'conditions' => self::CONDITIONS,
        ]);
    }

    // Menyimpan produk baru
    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($data['image']);
        }

        // Pemilik produk selalu user yang login (bukan dari input form)
        $data['user_id'] = auth()->id();

        Product::create($data);

        return redirect()
            ->route('seller.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    // Menampilkan form edit produk
    public function edit(Product $product)
    {
        $this->authorizeOwner($product);

        // Kategori aktif, ditambah kategori produk ini sendiri
        // (supaya tetap muncul walaupun kategorinya sudah dinonaktifkan)
        $categories = Category::where('is_active', true)
            ->orWhere('id', $product->category_id)
            ->orderBy('name')
            ->get();

        return view('seller.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'conditions' => self::CONDITIONS,
        ]);
    }

    // Menyimpan perubahan produk
    public function update(Request $request, Product $product)
    {
        // Cek pemilik DULU sebelum validasi
        $this->authorizeOwner($product);

        $rules = $this->rules();

        // Kalau kategori tidak diganti, jangan ditolak walau sudah nonaktif
        if ((int) $request->input('category_id') === $product->category_id) {
            $rules['category_id'] = ['required', Rule::exists('categories', 'id')];
        }

        $data = $request->validate($rules, $this->messages());

        if ($request->hasFile('image')) {
            // Hapus foto lama dari storage, lalu simpan yang baru
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($data['image']); // tidak upload foto baru, foto lama dipertahankan
        }

        // user_id tidak pernah diubah lewat form
        $product->update($data);

        return redirect()
            ->route('seller.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    // Pagar kepemilikan: bukan pemilik = 403
    private function authorizeOwner(Product $product): void
    {
        abort_if($product->user_id !== auth()->id(), 403, 'Ini bukan produk Anda.');
    }

    private function activeCategories()
    {
        return Category::where('is_active', true)->orderBy('name')->get();
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1000'],
            'condition' => ['required', Rule::in(self::CONDITIONS)],
            'weight' => ['required', 'integer', 'min:1'],
            'location' => ['required', 'string', 'max:100'],
            'stock' => ['required', 'integer', 'min:1'],

            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function messages(): array
    {
        return [
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
            
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format foto harus jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
