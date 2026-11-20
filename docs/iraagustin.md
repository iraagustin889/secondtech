Proyek: SECONDTECH (marketplace barang elektronik bekas)
Nama: Ira Agustin
Peran: Anggota 1, fondasi akun, katalog, dan profil
Teknologi: Laravel 13, Blade, Tailwind CSS, MySQL (ServBay), Git/GitHub

1. Ringkasan

Anggota 1 mengerjakan fondasi aplikasi yang dipakai fitur anggota lain: autentikasi, manajemen kategori dan produk, katalog, detail produk, dan profil pengguna. Issue #1 sampai #5 sudah selesai dan ter-merge ke main. Setelah itu dikerjakan perbaikan bug, fitur tambahan (foto profil, active state navbar, lupa password), dan migrasi database ke MySQL.

2. Fitur utama (Issue #1 sampai #5)
2.1 Akun dan autentikasi
Registrasi dan login dengan peran pembeli dan penjual.
Pesan error login dibuat seragam supaya tidak membocorkan email yang terdaftar.
Field role dan is_active tidak bisa diubah lewat form pengguna.
Akun admin tidak bisa dibuat lewat form daftar, hanya lewat seeder (disengaja demi keamanan).
2.2 Kategori dan produk (sisi penjual)
Penjual dapat menambah, mengubah, dan mengelola produk serta kategori.
Produk non-aktif hanya terlihat oleh pemiliknya.
2.3 Katalog
Pencarian, filter, dan pagination.
Hanya produk aktif dengan stok yang tampil.
2.4 Detail produk
Halaman detail dengan informasi produk dan ulasan.
Tombol Beli disembunyikan untuk produk milik sendiri.
2.5 Profil
Edit profil: nama, email, telepon, alamat, kota.
Data sensitif (role, is_active) dilindungi dari perubahan lewat form profil.
3. Fitur tambahan
3.1 Foto profil (PR #62, sudah ter-merge)
Migration kolom avatar pada tabel users.
Method avatarUrl() pada model User.
Validasi upload: jpg, jpeg, png, webp, maksimal 2 MB, dengan pesan error bahasa Indonesia.
Ganti dan hapus foto, file lama ikut dihapus dari storage.
Navbar menampilkan foto, atau inisial nama jika belum ada foto.
Konflik merge dengan fitur seller_bio milik anggota lain diselesaikan tanpa menghilangkan kedua fitur.
3.2 Active state menu navbar
Menu yang sedang dibuka (Pesanan Saya, Produk Saya, Penjualan Saya, notifikasi, profil) ditandai berbeda.
Memakai request()->routeIs(...). Halaman "Jual Barang" dikecualikan dari menu Produk Saya.
Branch: feat/navbar-aktif [sesuaikan status PR].
3.3 Lupa password
Route, PasswordResetController, dan dua halaman (lupa password, buat password baru).
Memakai mesin reset bawaan Laravel: token sekali pakai, kedaluwarsa 60 menit.
Pesan seragam baik email terdaftar maupun tidak.
Pengiriman email memakai driver log untuk pengembangan, bisa diganti ke SMTP lewat .env tanpa ubah kode.
Panduan uji untuk anggota tim sudah dibuat.
Branch: feat/lupa-password [sesuaikan status PR].
4. Perbaikan bug
Masalah	Penyebab	Perbaikan
Error no such table: reviews di detail produk	Migrasi fitur anggota lain belum dijalankan di laptop	php artisan migrate, dan aturan tim: setiap pull wajib migrate
Produk tertambah dobel saat Simpan diklik dua kali	Form bisa dikirim berulang	Tombol dinonaktifkan saat submit ("Menyimpan...")
Produk hilang setelah dibeli 1 padahal stok 100	Status produk selalu menjadi reserved di CreateOrderAction	Status active selama stok masih ada, reserved hanya jika stok habis
Form edit profil: upload foto gagal diam-diam	Tag <form> ganda sehingga enctype tidak terpakai	Dirapikan menjadi satu <form>

Temuan yang diputuskan sebagai rencana pengembangan: pembelian lebih dari 1 item (qty), karena alur saat ini memang mengunci qty = 1.

5. Infrastruktur dan database
Migrasi dari SQLite ke MySQL (ServBay), database secondtech dengan utf8mb4_unicode_ci.
Menyelesaikan rangkaian error konfigurasi: kredensial, DB_CONNECTION, MySQL belum menyala, migrasi belum dijalankan.
Seeder berisi akun demo (penjual, pembeli, admin), kategori, dan produk contoh. Semua akun demo berpassword password.
Rutinitas tim setiap pull: git pull origin main, php artisan migrate --seed, php artisan optimize:clear, npm run build.
6. Kolaborasi dan Git
Alur kerja: satu branch per fitur, lalu Pull Request ke main.
Menyelesaikan konflik merge pada ProfileController.php (fitur foto profil vs seller_bio).
Memperbaiki kesalahan Git: penulisan nama branch, commit yang masuk ke branch yang salah (dipindahkan dengan cherry-pick), dan penggunaan stash saat berpindah branch.
Insiden main rusak akibat konflik routes/web.php menjadi pelajaran: periksa konflik file bersama sebelum merge.
7. Pengujian
Test otomatis: 58 test lolos [perbarui jumlahnya setelah fitur terbaru].
Pengujian manual: alur register/login, kelola produk, katalog, detail, edit profil, upload foto, active state navbar, dan reset password.
8. Rencana pengembangan
Aturan kekuatan password (Password::defaults()).
Pembatasan percobaan login (rate limiting).
Login dengan Google.
Pembelian dengan jumlah (qty) lebih dari 1.
Verifikasi email saat registrasi.
Pengiriman email sungguhan untuk reset password (SMTP/Mailtrap).
9. Pelajaran
File yang dipakai bersama (routes/web.php, layout, controller profil) paling sering menimbulkan konflik, jadi perlu komunikasi sebelum mengubahnya.
Setiap anggota wajib git pull dan migrate agar tabel baru ada di laptop masing-masing.
Satu fitur per branch membuat PR bersih dan mudah di-review.