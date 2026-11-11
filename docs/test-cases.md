# Daftar Test Case SECONDTECH

| ID | Fitur | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|---|
| TC-01 | Register | Isi form daftar dengan data valid | Akun terbuat, role buyer | |
| TC-02 | Login | Masuk dengan akun valid | Masuk ke beranda | |
| TC-03 | Login akun nonaktif | Admin nonaktifkan akun, lalu login | Ditolak | |
| TC-04 | Tambah produk | Penjual isi form produk | Produk muncul di Produk Saya | |
| TC-05 | Katalog | Buka /products | Produk aktif tampil, filter dan pencarian jalan | |
| TC-06 | Checkout COD | Pembeli klik Beli sekarang, pilih COD | Pesanan terbuat, status cod_requested | |
| TC-07 | Checkout ekspedisi | Pilih ekspedisi, kirim referensi bayar | Status pending_payment lalu submitted | |
| TC-08 | Verifikasi pembayaran | Admin konfirmasi pembayaran | Status paid | |
| TC-09 | Proses dan resi | Penjual proses pesanan, input resi | Status processing lalu shipped | |
| TC-10 | Barang diterima | Pembeli klik Barang sudah saya terima | Status completed | |
| TC-11 | Ulasan | Pembeli kirim rating dan komentar | Ulasan tampil di detail produk | |
| TC-12 | Satu ulasan per pesanan | Kirim ulasan kedua | Ditolak | |
| TC-13 | Hak akses admin | Buyer buka /admin | 403 | |
| TC-14 | Admin monitoring | Admin buka /orders | 403 | |
| TC-15 | Moderasi produk | Admin sembunyikan produk dengan alasan | Status hidden, tercatat di log | |
| TC-16 | Biaya layanan | Admin ubah biaya, lalu checkout | Biaya pesanan baru sesuai, lama tidak berubah | |
| TC-17 | Laporan | Buka /admin/reports, filter tanggal | Total dan tabel sesuai | |