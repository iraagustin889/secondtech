<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Daftar kategori awal untuk barang elektronik second-hand
        $categories = [
            'Laptop',
            'Smartphone',
            'Tablet',
            'Kamera',
            'Konsol Game',
            'Audio & Headphone',
            'Aksesoris',
            'Lainnya',
        ];

        foreach ($categories as $name) {
            // updateOrCreate: kalau slug sudah ada, diperbarui. Kalau belum, dibuat.
            // Jadi seeder aman dijalankan berkali-kali tanpa data ganda.
            Category::updateOrCreate(
                ['slug' => Str::slug($name)], // kunci pencarian
                [
                    'name' => $name,
                    'is_active' => true,
                ]
            );
        }
    }
}
