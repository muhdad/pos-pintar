# AGENTS.md — AI Coding Assistant Guidelines (Laravel Edition)

## Project Overview
Aplikasi **Smart Cloud POS (Point of Sale)** berbasis **Monolithic Laravel** untuk pasar UMKM & Enterprise F&B serta Retail di Indonesia. Menggabungkan kecepatan transaksi kasir *Offline-First* (Alpine.js + Dexie.js) dengan *Backoffice AI Business Consultant* (Livewire).

## Tech Stack Utama
- **Backend & Framework:** Laravel 11/12 (PHP 8.2+)
- **Frontend Stack (TALL):** Tailwind CSS + Alpine.js + Laravel Blade + Livewire 3
- **Offline Storage (POS Terminal):** Alpine.js state + Dexie.js (IndexedDB) di `resources/js/pos-offline.js`
- **Database:** MySQL / PostgreSQL (via Laravel Eloquent ORM)
- **Authentication & RBAC:** Laravel Breeze + Custom Session Middleware untuk 4-Digit PIN Lock Kasir (`VerifyPosPin`)
- **AI Integration:** Laravel HTTP Client (`Http::withToken()`) / Prism PHP ke Gemini / OpenAI / Claude API dengan JSON Schema response
- **Payment & Hardware:** Midtrans Snap/Core API (QRIS Dinamis), WebUSB / Web Bluetooth API (ESC/POS Thermal Printer)

## Aturan Arsitektur & Coding (Guardrails)
1. **Strict Separation of Concerns (Backoffice vs POS):**
   - Route `/pos/*` menggunakan layout khusus fullscreen (`layouts/pos.blade.php`). Interaksi keranjang belanja (tambah item, hitung diskon/pajak, pilih modifier) **WAJIB** dijalankan di client-side menggunakan **Alpine.js + IndexedDB** agar instan (0ms latency tanpa *round-trip* request Livewire tiap klik item) dan tetap jalan saat internet mati.
   - Route `/backoffice/*` menggunakan **Livewire 3 Components** untuk manajemen produk, resep (BOM), laporan keuangan, dan AI Dashboard.
2. **Service Layer Pattern & Database Transactions:**
   - Jangan menaruh logika bisnis berat di dalam komponen Livewire atau Controller. Gunakan class di `app/Services/` (contoh: `OrderService`, `InventoryDeductionService`).
   - Setiap proses checkout dan pemotongan stok bahan baku wajib dibungkus dalam `DB::transaction()`.
3. **Financial & Stock Integrity:**
   - Di Laravel Migration, gunakan `unsignedBigInteger` atau `decimal(15, 2)` untuk harga, HPP (`cost_price`), dan nominal transaksi. Untuk takaran resep bahan baku (gram/ml), gunakan `decimal(10, 2)`.
   - Tabel `order_items` wajib menyimpan *snapshot* `unit_price` dan `unit_cost` saat transaksi terjadi.
4. **AI Token Efficiency:**
   - Gunakan `BusinessDataAggregator.php` (Eloquent Query Builder) untuk merangkum metrik penjualan menjadi JSON ringkas sebelum dikirim ke API LLM, lalu simpan hasilnya di tabel `ai_daily_reports` via Laravel Scheduled Command (`php artisan app:generate-ai-insights`).