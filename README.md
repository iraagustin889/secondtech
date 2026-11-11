# SECONDTECH

Marketplace barang elektronik second-hand berbasis web. Proyek kelompok mata kuliah (Laravel).

## Fitur

- **Pembeli:** daftar/login, katalog dengan pencarian dan filter, checkout (ekspedisi/COD), riwayat pembelian, konfirmasi barang diterima, rating dan ulasan.
- **Penjual:** kelola produk (tambah, ubah, sembunyikan, hapus, foto), pesanan masuk, proses pesanan, nomor resi, kesepakatan COD.
- **Admin (monitoring):** dashboard, kelola pengguna (aktif/nonaktif), moderasi produk beserta log aktivitas, pengaturan biaya layanan, laporan transaksi, verifikasi pembayaran.

## Kebutuhan Sistem

- PHP 8.3 atau lebih baru
- Composer 2
- MySQL 8
- Node.js 22 LTS (atau lebih baru) dan npm
- Git

## Instalasi

```bash
git clone https://github.com/iraagustin889/secondtech.git
cd secondtech
composer install
cp .env.example .env        # Windows CMD: copy .env.example .env
php artisan key:generate
```

Buat database kosong bernama `secondtech` di MySQL, lalu atur `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=secondtech
DB_USERNAME=root
DB_PASSWORD=
```

Lanjutkan:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
```

Untuk mengulang dari awal (data lama dihapus):

```bash
php artisan migrate:fresh --seed
```

> Perhatian: perintah ini **menghapus seluruh isi database** `secondtech`, lalu membuatnya ulang beserta data contoh.

## Menjalankan Aplikasi

Buka dua terminal:

```bash
npm run dev
```

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun Demo

Dibuat otomatis oleh `php artisan migrate --seed`. Password semua akun: `password`.

| Peran | Email |
|---|---|
| Admin | admin@secondtech.test |
| Penjual | seller@secondtech.test |
| Pembeli | buyer@secondtech.test |

Seeder juga membuat kategori dan beberapa data pesanan contoh.

## Menjalankan Tes

Tes memakai SQLite in-memory (lihat `phpunit.xml`), jadi tidak menyentuh database lokal.

```bash
php artisan test
```

## Alur Kerja Tim

- Satu fitur = satu branch (`feature/...`), dibuat dari `main` terbaru.
- Merge ke `main` lewat Pull Request, sertakan `Refs #nomor`; PR terakhir sebuah issue memakai `Closes #nomor`.
- Daftar test case ada di [docs/test-cases.md](docs/test-cases.md).

## Tim

- Anggota 1: akun, produk, katalog
- Anggota 2: checkout, pembayaran, pengiriman, riwayat pesanan
- Anggota 3: admin, biaya layanan, laporan, ulasan, pengujian