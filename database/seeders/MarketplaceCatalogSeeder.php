<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MarketplaceCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sellers = collect([
            [
                'name' => 'SecondTech Banyuwangi',
                'email' => 'seller@secondtech.test',
                'phone' => '081234567890',
                'location' => 'Banyuwangi',
            ],
            [
                'name' => 'Gadget Pilihan Surabaya',
                'email' => 'gadget.surabaya@secondtech.test',
                'phone' => '081298765432',
                'location' => 'Surabaya',
            ],
            [
                'name' => 'KameraKita Malang',
                'email' => 'kamera.malang@secondtech.test',
                'phone' => '081377889900',
                'location' => 'Malang',
            ],
        ])->mapWithKeys(function (array $seller): array {
            $user = User::updateOrCreate(
                ['email' => $seller['email']],
                [
                    ...$seller,
                    'password' => Hash::make('password'),
                    'role' => 'seller',
                    'is_active' => true,
                ],
            );

            return [$seller['email'] => $user];
        });

        $products = [
            [
                'seller' => 'seller@secondtech.test',
                'category' => 'Laptop',
                'name' => 'MacBook Air M1 2020 8GB/256GB',
                'description' => 'MacBook Air M1 warna Silver, RAM 8GB dan SSD 256GB. Kondisi terawat, semua tombol dan port berfungsi normal. Sudah termasuk charger bawaan.',
                'price' => 7_800_000,
                'condition' => 'Bekas baik',
                'weight' => 1_290,
                'location' => 'Banyuwangi',
            ],
            [
                'seller' => 'gadget.surabaya@secondtech.test',
                'category' => 'Smartphone',
                'name' => 'iPhone 13 128GB Midnight',
                'description' => 'iPhone 13 128GB warna Midnight. Face ID, kamera, speaker, dan layar berfungsi normal. Unit, kabel data, dan dus tersedia.',
                'price' => 6_100_000,
                'condition' => 'Bekas baik',
                'weight' => 350,
                'location' => 'Surabaya',
            ],
            [
                'seller' => 'gadget.surabaya@secondtech.test',
                'category' => 'Smartphone',
                'name' => 'Samsung Galaxy S22 5G 128GB',
                'description' => 'Galaxy S22 5G 128GB warna Phantom Black. Layar AMOLED jernih, kamera dan jaringan 5G normal. Cocok untuk pemakaian harian.',
                'price' => 4_900_000,
                'condition' => 'Bekas baik',
                'weight' => 360,
                'location' => 'Surabaya',
            ],
            [
                'seller' => 'seller@secondtech.test',
                'category' => 'Tablet',
                'name' => 'iPad Air 4 Wi-Fi 64GB Sky Blue',
                'description' => 'iPad Air generasi keempat Wi-Fi 64GB. Touch ID, layar, dan baterai normal. Termasuk kabel pengisi daya.',
                'price' => 5_200_000,
                'condition' => 'Bekas baik',
                'weight' => 600,
                'location' => 'Banyuwangi',
            ],
            [
                'seller' => 'kamera.malang@secondtech.test',
                'category' => 'Kamera',
                'name' => 'Fujifilm X-T20 Kit 15-45mm',
                'description' => 'Kamera mirrorless Fujifilm X-T20 dengan lensa kit 15-45mm. Sensor, autofocus, LCD, dan tombol berfungsi baik. Cocok untuk belajar fotografi.',
                'price' => 6_500_000,
                'condition' => 'Bekas baik',
                'weight' => 900,
                'location' => 'Malang',
            ],
            [
                'seller' => 'seller@secondtech.test',
                'category' => 'Konsol Game',
                'name' => 'Nintendo Switch OLED White',
                'description' => 'Nintendo Switch OLED warna putih, layar OLED dan Joy-Con berfungsi normal. Termasuk dock, adaptor, dan strap Joy-Con.',
                'price' => 3_700_000,
                'condition' => 'Bekas baik',
                'weight' => 1_100,
                'location' => 'Banyuwangi',
            ],
            [
                'seller' => 'gadget.surabaya@secondtech.test',
                'category' => 'Audio & Headphone',
                'name' => 'Sony WH-1000XM4 Noise Cancelling',
                'description' => 'Headphone Sony WH-1000XM4 dengan noise cancelling aktif. Baterai, Bluetooth, mikrofon, dan tombol kontrol berfungsi normal.',
                'price' => 2_100_000,
                'condition' => 'Bekas baik',
                'weight' => 500,
                'location' => 'Surabaya',
            ],
            [
                'seller' => 'seller@secondtech.test',
                'category' => 'Aksesoris',
                'name' => 'Logitech MX Keys Mini Keyboard',
                'description' => 'Keyboard wireless Logitech MX Keys Mini layout US. Semua tombol, Bluetooth, dan USB-C charging berfungsi normal.',
                'price' => 900_000,
                'condition' => 'Seperti baru',
                'weight' => 700,
                'location' => 'Banyuwangi',
            ],
            [
                'seller' => 'seller@secondtech.test',
                'category' => 'Komponen & GPU',
                'name' => 'ASUS Dual Radeon RX 6600 8GB',
                'description' => 'Kartu grafis Radeon RX 6600 8GB. Dipakai ringan untuk gaming, suhu stabil, port HDMI dan DisplayPort berfungsi normal. Termasuk dus.',
                'price' => 2_650_000,
                'condition' => 'Bekas baik',
                'weight' => 950,
                'location' => 'Banyuwangi',
            ],
            [
                'seller' => 'kamera.malang@secondtech.test',
                'category' => 'Kamera',
                'name' => 'GoPro HERO10 Black',
                'description' => 'GoPro HERO10 Black lengkap dengan baterai, frame, dan kabel USB-C. Rekam video 5.3K, stabilisasi, layar depan, dan layar belakang normal.',
                'price' => 3_100_000,
                'condition' => 'Bekas baik',
                'weight' => 400,
                'location' => 'Malang',
            ],
            [
                'seller' => 'gadget.surabaya@secondtech.test',
                'category' => 'Lainnya',
                'name' => 'Kindle Paperwhite 11th Gen 8GB',
                'description' => 'Kindle Paperwhite generasi ke-11 8GB dengan layar e-ink dan lampu baca. Cocok untuk membaca ebook, baterai masih awet.',
                'price' => 1_600_000,
                'condition' => 'Bekas baik',
                'weight' => 400,
                'location' => 'Surabaya',
            ],
        ];

        foreach ($products as $item) {
            $category = Category::query()->where('name', $item['category'])->firstOrFail();
            $seller = $sellers->get($item['seller']);

            Product::updateOrCreate(
                ['user_id' => $seller->id, 'name' => $item['name']],
                [
                    'category_id' => $category->id,
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'condition' => $item['condition'],
                    'weight' => $item['weight'],
                    'location' => $item['location'],
                    'stock' => 1,
                    'status' => 'active',
                ],
            );
        }

        Product::query()
            ->whereHas('seller', fn ($query) => $query->where('email', 'like', '%@example.%'))
            ->where('status', 'active')
            ->update(['status' => 'hidden']);
    }
}
