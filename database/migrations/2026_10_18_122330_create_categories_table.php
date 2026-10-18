<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        $table->id();

        // Nama kategori, contoh: Laptop, Smartphone
        $table->string('name');

        // Versi URL-friendly dari nama, contoh: laptop. Harus unik.
        $table->string('slug')->unique();

        // Status aktif/nonaktif (dipakai di Issue #2).
        // Kategori nonaktif tidak bisa dipilih di produk baru.
        $table->boolean('is_active')->default(true);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
