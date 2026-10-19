<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@secondtech.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Penjual Demo',
            'email' => 'seller@secondtech.test',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'phone' => '081234567890',
            'location' => 'Banyuwangi',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Pembeli Demo',
            'email' => 'buyer@secondtech.test',
            'password' => Hash::make('password'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

               // Seeder kategori harus jalan sebelum seeder order (produk butuh kategori)
        $this->call([
            CategorySeeder::class,
            OrderSeeder::class,
        ]);
    }
}
