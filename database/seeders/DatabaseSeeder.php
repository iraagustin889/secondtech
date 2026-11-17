<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@secondtech.test'], [
            'name' => 'Admin',
            'email' => 'admin@secondtech.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'seller@secondtech.test'], [
            'name' => 'SecondTech Banyuwangi',
            'email' => 'seller@secondtech.test',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'phone' => '081234567890',
            'location' => 'Banyuwangi',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'buyer@secondtech.test'], [
            'name' => 'Pembeli Demo',
            'email' => 'buyer@secondtech.test',
            'password' => Hash::make('password'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'admin@gmail.com'], [
            'name' => 'Admin Dua',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Kategori harus jalan sebelum katalog agar setiap produk memiliki kategori yang benar.
        $this->call([
            CategorySeeder::class,
            ServiceFeeSeeder::class,
            MarketplaceCatalogSeeder::class,
            ConversationSeeder::class,
            MessageSeeder::class,
        ]);
    }
}
