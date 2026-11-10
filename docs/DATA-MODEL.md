# Data Model & Schema Specification (Laravel Edition)

Dokumen ini mendefinisikan skema basis data relasional Laravel Eloquent untuk POS Pintar dengan dukungan *Multi-Tenancy*, *BOM Recipe Inventory*, dan transaksi *Offline-First*.

---

## 1. Konvensi Tipe Data & Presisi Keuangan (Sesuai AGENTS.md)

1. **Harga & Nilai Uang:** Menggunakan `decimal(15, 2)` atau `unsignedBigInteger` untuk `selling_price`, `cost_price` (HPP), `subtotal`, `discount_amount`, `tax_amount`, dan `final_amount`.
2. **Takaran Resep / Bahan Baku:** Menggunakan `decimal(12, 4)` untuk presisi gram/ml (misal: 18.5 gram biji kopi).
3. **Snapshot Finansial:** Kolom `unit_price` dan `unit_cost` pada tabel `order_items` **wajib** mencatat nilai riil pada saat transaksi dibuat agar laporan laba kotor tidak terdistorsi saat harga barang naik di masa depan.
4. **Primary Key Transaksi:** Tabel `orders` menggunakan `char(36)` (UUID v4) atau string untuk mendukung pembuatan order di sisi client (browser kasir) saat offline.

---

## 2. Struktur Tabel & Relasi

### A. Tabel `tenants`
- `id`: `char(36)` / UUID (Primary Key)
- `name`: `string(150)` (Nama Brand / Usaha)
- `slug`: `string(100)` (Unique)
- `owner_phone`: `string(30)` (Nomor WhatsApp Owner)
- `plan_tier`: `string(50)` (Default: `PRO_BUNDLE`)
- `is_active`: `boolean` (Default: `true`)
- `timestamps`

### B. Tabel `users`
- `id`: `unsignedBigInteger` (Primary Key)
- `tenant_id`: `char(36)` (Foreign Key ke `tenants.id`, cascade)
- `name`: `string(150)`
- `email`: `string(150)` (Unique)
- `password`: `string(255)` (Hash login Backoffice)
- `phone`: `string(30)` (Nullable)
- `pin_hash`: `string(255)` (4-digit PIN ter-hash untuk fast cashier switch & otorisasi supervisor)
- `role`: `enum('OWNER', 'MANAGER', 'CASHIER', 'WAREHOUSE', 'KITCHEN')` (Default: `CASHIER`)
- `is_active`: `boolean` (Default: `true`)
- `remember_token`, `timestamps`

### C. Tabel `outlets`
- `id`: `char(36)` / UUID (Primary Key)
- `tenant_id`: `char(36)` (Foreign Key ke `tenants.id`, cascade)
- `name`: `string(150)` (Contoh: "Cabang Tebet", "Gudang Utama")
- `type`: `enum('STORE', 'CENTRAL_KITCHEN', 'WAREHOUSE')` (Default: `STORE`)
- `address`: `text` (Nullable)
- `phone`: `string(30)` (Nullable)
- `timestamps`

### D. Tabel `user_outlets` (Pivot Penugasan Karyawan ke Outlet)
- `id`: `unsignedBigInteger` (Primary Key)
- `user_id`: `unsignedBigInteger` (Foreign Key ke `users.id`, cascade)
- `outlet_id`: `char(36)` (Foreign Key ke `outlets.id`, cascade)
- `unique(['user_id', 'outlet_id'])`

### E. Tabel `terminals` (Register Kasir)
- `id`: `char(36)` / UUID (Primary Key)
- `outlet_id`: `char(36)` (Foreign Key ke `outlets.id`, cascade)
- `name`: `string(100)` (Contoh: "Kasir Barista 01")
- `device_uid`: `string(100)` (Nullable, identifier hardware browser)
- `is_active`: `boolean` (Default: `true`)
- `timestamps`

### F. Tabel `tables` (Denah Meja F&B)
- `id`: `char(36)` / UUID (Primary Key)
- `outlet_id`: `char(36)` (Foreign Key ke `outlets.id`, cascade)
- `name`: `string(50)` (Contoh: "Meja 01")
- `is_occupied`: `boolean` (Default: `false`)
- `current_order_id`: `char(36)` (Nullable)
- `timestamps`

### G. Tabel `shifts` (Sesi Laci Kasir)
- `id`: `char(36)` / UUID (Primary Key)
- `outlet_id`: `char(36)` (Foreign Key ke `outlets.id`, cascade)
- `terminal_id`: `char(36)` (Foreign Key ke `terminals.id`, cascade)
- `user_id`: `unsignedBigInteger` (Foreign Key ke `users.id`, cascade)
- `start_time`: `timestamp`
- `end_time`: `timestamp` (Nullable)
- `start_cash`: `decimal(15, 2)` (Modal Awal)
- `expected_cash`: `decimal(15, 2)` (Nullable, kalkulasi sistem)
- `actual_cash`: `decimal(15, 2)` (Nullable, hasil blind closing kasir)
- `cash_difference`: `decimal(15, 2)` (Nullable, selisih +/-)
- `notes`: `text` (Nullable)
- `timestamps`

### H. Tabel `categories`
- `id`: `char(36)` / UUID (Primary Key)
- `tenant_id`: `char(36)` (Foreign Key ke `tenants.id`, cascade)
- `name`: `string(100)`
- `icon`: `string(50)` (Nullable)
- `timestamps`

### I. Tabel `products`
- `id`: `char(36)` / UUID (Primary Key)
- `tenant_id`: `char(36)` (Foreign Key ke `tenants.id`, cascade)
- `category_id`: `char(36)` (Nullable, Foreign Key ke `categories.id`, set null)
- `sku`: `string(50)` (Nullable)
- `barcode`: `string(100)` (Nullable)
- `name`: `string(150)`
- `description`: `text` (Nullable)
- `selling_price`: `decimal(15, 2)`
- `cogs`: `decimal(15, 2)` (HPP Moving Average)
- `image_url`: `string(255)` (Nullable)
- `track_stock`: `boolean` (Default: `true`)
- `is_active`: `boolean` (Default: `true`)
- `timestamps`

### J. Tabel `raw_materials` (Bahan Baku Resep)
- `id`: `char(36)` / UUID (Primary Key)
- `tenant_id`: `char(36)` (Foreign Key ke `tenants.id`, cascade)
- `category_id`: `char(36)` (Nullable)
- `sku`: `string(50)` (Nullable)
- `name`: `string(150)` (Contoh: "Biji Kopi Arabica Gayo", "Susu Fresh Milk")
- `unit`: `string(20)` ("gram", "ml", "pcs", "kg")
- `average_cost`: `decimal(15, 2)` (HPP per unit)
- `minimum_stock`: `decimal(12, 4)` (Batas peringatan restock)
- `timestamps`

### K. Tabel `recipes` (Bill of Materials / BOM)
- `id`: `char(36)` / UUID (Primary Key)
- `product_id`: `char(36)` (Foreign Key ke `products.id`, cascade)
- `raw_material_id`: `char(36)` (Foreign Key ke `raw_materials.id`, cascade)
- `quantity`: `decimal(12, 4)` (Jumlah pemakaian, contoh: 18.0000 gram)
- `unique(['product_id', 'raw_material_id'])`
- `timestamps`

### L. Tabel `outlet_stocks` (Stok Bahan Baku Riil per Cabang)
- `id`: `char(36)` / UUID (Primary Key)
- `outlet_id`: `char(36)` (Foreign Key ke `outlets.id`, cascade)
- `raw_material_id`: `char(36)` (Foreign Key ke `raw_materials.id`, cascade)
- `current_stock`: `decimal(12, 4)` (Default: 0)
- `unique(['outlet_id', 'raw_material_id'])`
- `timestamps`

### M. Tabel `stock_movements` (Audit Kartu Stok)
- `id`: `char(36)` / UUID (Primary Key)
- `outlet_stock_id`: `char(36)` (Foreign Key ke `outlet_stocks.id`, cascade)
- `type`: `enum('SALE', 'RESTOCK_OCR', 'RESTOCK_MANUAL', 'TRANSFER_IN', 'TRANSFER_OUT', 'WASTE', 'OPNAME_ADJUSTMENT')`
- `quantity_delta`: `decimal(12, 4)` (Perubahan stok +/-)
- `balance_after`: `decimal(12, 4)`
- `reference_id`: `string(100)` (Nullable, e.g. order_id)
- `notes`: `string(255)` (Nullable)
- `created_at`: `timestamp`

### N. Tabel `orders` (Transaksi Kasir)
- `id`: `char(36)` / UUID (Primary Key - Generated by client/server)
- `tenant_id`: `char(36)` (Foreign Key ke `tenants.id`, cascade)
- `outlet_id`: `char(36)` (Foreign Key ke `outlets.id`, cascade)
- `shift_id`: `char(36)` (Nullable, Foreign Key ke `shifts.id`, set null)
- `user_id`: `unsignedBigInteger` (Nullable, Foreign Key ke `users.id`, set null)
- `table_id`: `char(36)` (Nullable, Foreign Key ke `tables.id`, set null)
- `order_number`: `string(50)` (Format: [OUTLET]-[DATE]-[SEQ])
- `status`: `enum('DRAFT', 'PENDING', 'PAID', 'CANCELLED', 'VOIDED', 'REFUNDED')` (Default: `PENDING`)
- `total_amount`: `decimal(15, 2)`
- `discount_amount`: `decimal(15, 2)` (Default: 0)
- `tax_amount`: `decimal(15, 2)` (Default: 0)
- `final_amount`: `decimal(15, 2)`
- `customer_name`: `string(100)` (Nullable)
- `customer_phone`: `string(30)` (Nullable, untuk struk WA)
- `synced_at`: `timestamp` (Nullable, tanda order sudah disinkronkan dari lokal offline)
- `timestamps`

### O. Tabel `order_items`
- `id`: `char(36)` / UUID (Primary Key)
- `order_id`: `char(36)` (Foreign Key ke `orders.id`, cascade)
- `product_id`: `char(36)` (Foreign Key ke `products.id`, restrict)
- `quantity`: `integer`
- `unit_price`: `decimal(15, 2)` (**Snapshot harga jual**)
- `unit_cost`: `decimal(15, 2)` (**Snapshot HPP saat order dibuat**)
- `subtotal`: `decimal(15, 2)`
- `notes`: `string(255)` (Nullable, e.g. "Less sugar, extra hot")
- `timestamps`

### P. Tabel `payments`
- `id`: `char(36)` / UUID (Primary Key)
- `order_id`: `char(36)` (Foreign Key ke `orders.id`, cascade)
- `method`: `enum('CASH', 'QRIS', 'DEBIT', 'CREDIT', 'TRANSFER')`
- `amount`: `decimal(15, 2)`
- `status`: `enum('PENDING', 'SETTLED', 'FAILED', 'EXPIRED')` (Default: `SETTLED`)
- `reference_no`: `string(100)` (Nullable)
- `paid_at`: `timestamp`
- `timestamps`

### Q. Tabel `audit_logs` (Fraud Detection & Security)
- `id`: `char(36)` / UUID (Primary Key)
- `tenant_id`: `char(36)` (Foreign Key ke `tenants.id`, cascade)
- `user_id`: `unsignedBigInteger` (Nullable, Foreign Key ke `users.id`, set null)
- `action`: `string(50)` (VOID_ORDER, MANUAL_DISCOUNT, OPEN_DRAWER, CASH_DROP)
- `description`: `text`
- `metadata`: `json` (Nullable)
- `created_at`: `timestamp`

### R. Tabel `ai_daily_reports` (Rangkuman Konsultan AI Harian)
- `id`: `char(36)` / UUID (Primary Key)
- `tenant_id`: `char(36)` (Foreign Key ke `tenants.id`, cascade)
- `outlet_id`: `char(36)` (Nullable)
- `report_date`: `date`
- `total_revenue`: `decimal(15, 2)`
- `total_cogs`: `decimal(15, 2)`
- `gross_profit`: `decimal(15, 2)`
- `total_transactions`: `integer`
- `metrics_summary`: `json` (Ringkasan penjualan, dead stock, best seller)
- `ai_insights`: `json` (Rekomendasi taktis & evaluasi anomali dari AI)
- `timestamps`

