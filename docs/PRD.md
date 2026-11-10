# Product Requirements Document (PRD): Smart Cloud POS Pintar (Laravel Edition)

Dokumen ini berisi cetak biru (*blueprint*) fitur, arsitektur hak akses akun (*Account Scoping & RBAC*), alur kerja (*user & system flow*), dan strategi produk aplikasi **POS Pintar** berbasis Laravel Monolith (TALL Stack).

---

## 1. Unique Selling Proposition (Strategi Penggeser Pasar)

| Parameter | Kompetitor (Moka / Majoo / Olsera) | Smart Cloud POS Pintar (Disruptor) |
| :--- | :--- | :--- |
| **Arsitektur & Kecepatan** | Cloud-dependent / Aplikasi native tablet yang berat (*bloated*). | **Hybrid Monolith + Offline-First**: Ringan, jalan di browser Android/iPad/PC apa pun, 0ms latency kasir via Alpine.js + Dexie.js. |
| **Skema Harga** | Bayar per-outlet/bulan (mahal saat ekspansi) atau wajib tahunan. | **Multi-Outlet Bundle** (1 langganan untuk 1–3 cabang) & paket bulanan UMKM ramah kantong. |
| **Fitur Tanpa Add-on** | Fitur resep BOM, WhatsApp receipt, dan AI dikunci add-on mahal. | **All-Inclusive**: AI Business Consultant, WA Struk, Manajemen Resep (BOM), dan Central Warehouse sudah ada sejak paket dasar. |
| **Manajemen Resep & Stok** | Hanya potong stok sederhana; integrasi HPP lemah. | **Deep Warehouse & Automatic BOM**: Potong gramasi/ml bahan baku otomatis dengan *Moving Average COGS (HPP)*. |
| **AI Co-Pilot Bawaan** | Tidak ada atau sekadar chatbot generik. | **AI Consultant & OCR Restock**: Foto nota belanja pasar langsung update stok gudang & konsultan bisnis berbasis data penjualan riil. |

---

## 2. Arsitektur Akun, Scoping & RBAC (Role-Based Access Control)

### A. Hierarki Multi-Tenancy Data
1. **Tenant (Brand / Owner):** Entitas kepemilikan bisnis utama (contoh: "Kopi Nusantara Group").
2. **Outlet (Cabang Fisik / Central Kitchen / Gudang):** Unit operasional di bawah Tenant.
3. **Terminal (Register POS):** Perangkat kasir aktif (misal: "Terminal 01 Kasir").
4. **Shift:** Sesi laci uang kasir terikat pada kasir dan terminal aktif.
5. **Users & Roles:**
   - **OWNER:** Pemilik usaha, akses penuh ke Backoffice, HPP, Multi-Outlet, AI Consultant, & ganti setelan.
   - **MANAGER / SUPERVISOR:** Operasional cabang, otorisasi Void/Refund/Diskon manual via PIN, audit shift.
   - **CASHIER:** Khusus operasional kasir (POS Mode), transaksi, *blind closing* kas.
   - **WAREHOUSE:** Kelola stok gudang, penerimaan barang supplier, scan nota OCR.
   - **KITCHEN (KDS):** Layar dapur / barista untuk memantau status pesanan (Pending, Cooking, Done).

### B. Dual-Mode Session (Backoffice vs POS Screen)
- **Backoffice Mode (`/backoffice/*`):** Ditangani oleh Livewire 3 Components. Menampilkan dashboard analitik, manajemen produk, resep BOM, laporan keuangan HPP, dan konsultasi AI.
- **POS Mode (`/pos/*`):** Fullscreen layout (`layouts/pos.blade.php`), bebas distraksi menu admin. Dilengkapi 4-digit PIN lock kasir (`VerifyPosPin` middleware).
- **Fast Switch Staff via 4-Digit PIN:** Tablet kasir bersama (*shared tablet*) dapat berpindah kasir/supervisor secara instan tanpa perlu logout akun browser.

---

## 3. Fitur Utama MVP

1. **Kasir Offline-First (Front-Office):**
   - Transaksi instan dengan Alpine.js + Dexie.js (IndexedDB).
   - Cetak struk Bluetooth / USB ESC/POS Thermal Printer.
   - Pembayaran Tunai, Transfer, EDC, dan QRIS Dinamis.
   - Auto-sync ke Cloud Laravel saat internet kembali online.
2. **Resep Bahan Baku (BOM) & Inventori Otomatis:**
   - Pemotongan stok bahan baku per gram/ml saat produk terjual.
   - Moving Average HPP (COGS) dihitung otomatis.
   - Notifikasi stok menipis (*Low Stock Alert*).
3. **AI Business Consultant & OCR Scanner:**
   - OCR Scan Faktur/Nota Belanja Supplier untuk restock otomatis.
   - Rangkuman performa bisnis harian via scheduled command (`php artisan app:generate-ai-insights`).
   - Tanya jawab analisis performa toko berbasis data riil.
4. **Shift & Blind Closing Laci Kas:**
   - Input modal awal kasir (*cash float*).
   - Kasir hanya menginput hitungan uang fisik saat tutup shift tanpa melihat total sistem untuk mencegah kecurangan (*blind closing*).

