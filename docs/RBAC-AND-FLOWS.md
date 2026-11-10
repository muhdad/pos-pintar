# RBAC & Operational Flows (Laravel Edition)

Dokumen ini menjelaskan alur hak akses (*Role-Based Access Control*), dual-mode session (Backoffice vs POS Screen), dan alur kerja utama aplikasi POS Pintar.

---

## 1. Matriks Hak Akses (RBAC)

| Modul / Kemampuan | OWNER | MANAGER / SPV | CASHIER (Kasir) | WAREHOUSE (Gudang) | KITCHEN (Dapur) |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Akses Backoffice (`/backoffice/*`)** | Full Akses | Terbatas Cabang | ❌ Dilarang | Terbatas Gudang | ❌ Dilarang |
| **Akses POS Kasir (`/pos/*`)** | Buka via Switcher | Buka via Switcher | Layar Utama | ❌ Dilarang | ❌ Dilarang |
| **Lihat Laba-Rugi & HPP (COGS)** | ✅ Ya | ❌ Tidak (Hanya Omzet) | ❌ Dilarang Keras | ❌ Hanya Belanja Stok | ❌ Tidak |
| **Buka/Tutup Shift Kasir** | ✅ Ya | ✅ Ya | ✅ Blind Closing | ❌ Tidak | ❌ Tidak |
| **Void / Batalkan Pesanan Selesai** | ✅ Bebas | ✅ Otorisasi PIN | ⚠️ Butuh PIN SPV | ❌ Tidak | ❌ Tidak |
| **Diskon Manual di Kasir** | ✅ Bebas | ✅ Otorisasi PIN | ⚠️ Butuh PIN SPV | ❌ Tidak | ❌ Tidak |
| **AI Business Consultant & Insights** | ✅ Full Akses | ⚠️ Terbatas Stok | ❌ Tidak | ❌ Tidak | ❌ Tidak |
| **Scan OCR Faktur Belanja Gudang** | ✅ Ya | ✅ Ya | ❌ Tidak | ✅ Ya | ❌ Tidak |

---

## 2. Dual-Mode Session & Fast Staff PIN Switching

### A. Dual-Mode Architecture
- **Web Session (Backoffice):** Menggunakan autentikasi standar Laravel Breeze (`auth` middleware) untuk akses halaman administrasi web Livewire.
- **POS Mode (`/pos/*`):** Menggunakan middleware `VerifyPosPin`. Tampilan fullscreen, navigasi admin disembunyikan.
- **Kembali ke Backoffice:** Mengharuskan verifikasi password atau PIN Owner untuk mencegah kasir mengakses pembukuan saat menggunakan laptop/tablet bersama.

### B. Fast Cashier Switching (4-Digit PIN)
- Tablet kasir di toko tidak perlu login/logout email saat pergantian shift.
- Kasir cukup mengunci layar (*Lock Terminal*) dan kasir pengganti memasukkan 4-Digit PIN.
- Sistem mencatat `shift_id` dan `user_id` yang sesuai pada setiap transaksi order dan audit log.

