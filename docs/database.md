# Dokumentasi Database SECONDTECH

Database: MySQL (`secondtech`). Struktur dibuat lewat migration di `database/migrations`.

## Diagram ERD

```mermaid
erDiagram
    users ||--o{ products : "menjual"
    users ||--o{ orders : "membeli (buyer_id)"
    users ||--o{ reviews : "menulis (buyer_id)"
    users ||--o{ reviews : "diulas (seller_id)"
    users ||--o{ admin_logs : "dicatat (admin_id)"
    categories ||--o{ products : "mengelompokkan"
    products ||--o{ order_items : "dipesan"
    orders ||--|{ order_items : "berisi"
    orders ||--o| payments : "dibayar"
    orders ||--o| shippings : "dikirim"
    orders ||--o| reviews : "diulas"
    products ||--o{ reviews : "diulas"

    users {
        bigint id PK
        string name
        string email UK
        string password
        string role "buyer, seller, admin"
        string phone
        text address
        string location
        boolean is_active
    }
    categories {
        bigint id PK
        string name
        string slug
        boolean is_active
    }
    products {
        bigint id PK
        bigint user_id FK
        bigint category_id FK
        string name
        text description
        bigint price
        string condition
        int weight
        string location
        int stock
        string status "active, reserved, sold, hidden"
        string image
    }
    orders {
        bigint id PK
        string order_number UK
        bigint buyer_id FK
        decimal subtotal
        decimal service_fee
        decimal shipping_cost
        decimal total
        string status
        string shipping_method
        timestamp cancelled_at
    }
    order_items {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        decimal price_snapshot
        int qty
    }
    reviews {
        bigint id PK
        bigint order_id FK "unik"
        bigint product_id FK
        bigint buyer_id FK
        bigint seller_id FK
        tinyint rating "1-5"
        text comment
    }
    service_fee_configurations {
        bigint id PK
        string type "nominal, percentage"
        decimal value
        boolean is_active
    }
    admin_logs {
        bigint id PK
        bigint admin_id FK
        string action
        string target_type
        bigint target_id
        text reason
    }
```

Tabel `payments` dan `shippings` milik fitur pembayaran dan pengiriman (relasi satu-ke-satu dengan `orders`); kolom detailnya mengikuti migration masing-masing.

## Penjelasan Tabel

| Tabel | Fungsi |
|---|---|
| users | Akun pengguna dengan role buyer, seller, atau admin; `is_active` untuk menonaktifkan akun |
| categories | Kategori produk elektronik; kategori nonaktif tidak bisa dipilih |
| products | Produk yang dijual; `status` mengatur tampil di katalog |
| orders | Pesanan; menyimpan subtotal, biaya layanan (snapshot), ongkir, total, dan status |
| order_items | Produk dalam pesanan, dengan harga saat checkout (`price_snapshot`) |
| payments | Pembayaran per pesanan, diverifikasi admin |
| shippings | Pengiriman per pesanan: ekspedisi dengan resi, atau kesepakatan COD |
| reviews | Rating 1-5 dan komentar; `order_id` unik sehingga satu pesanan hanya satu ulasan |
| service_fee_configurations | Riwayat pengaturan biaya layanan; yang `is_active` dipakai checkout |
| admin_logs | Catatan aktivitas admin (moderasi produk, ubah biaya layanan) |

## Alur Status Pesanan

- Ekspedisi: `pending_payment` → `paid` → `processing` → `shipped` → `received` → `completed`
- COD: `cod_requested` → `cod_agreed` → `completed`
- Pembatalan: `cancelled` (dari `pending_payment` atau `cod_requested`)