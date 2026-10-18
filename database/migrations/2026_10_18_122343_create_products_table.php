<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Pemilik produk (penjual). Kalau user dihapus, produknya ikut terhapus.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Kategori produk. Kategori tidak boleh dihapus selama masih dipakai produk.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            $table->string('name');                       // Nama produk
            $table->text('description')->nullable();      // Deskripsi (boleh kosong)
            $table->unsignedBigInteger('price');          // Harga dalam rupiah (bilangan bulat)
            $table->string('condition');                  // Kondisi barang, contoh: like new, bekas baik
            $table->unsignedInteger('weight');            // Berat (gram), dipakai untuk ongkir
            $table->string('location');                   // Lokasi barang / kota penjual
            $table->unsignedInteger('stock')->default(1); // Stok, default 1 (barang second biasanya satuan)
            $table->string('status')->default('active');  // active / hidden (untuk fitur sembunyikan)
            $table->string('image')->nullable();          // Path foto produk (diisi saat fitur upload dibuat)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
