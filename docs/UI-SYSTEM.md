# UI Design System: Modern Soft Sky-Blue

Dokumen ini mendefinisikan panduan visual dan sistem komponen antarmuka pengguna (UI) untuk aplikasi POS Pintar.

---

## 1. Tema Warna & Nuansa Visual

- **Background:** Nuansa *soft airy sky-blue* yang ramah mata UMKM:
  `bg-gradient-to-br from-[#e0f0fe] via-[#eaf4fe] to-[#f4f9ff]`
- **Cards & Surfaces:**
  Kartu putih dengan rounded sudut halus (`rounded-2xl` / `rounded-3xl`), border halus (`border border-blue-100/60`), dan bayangan lembut (`shadow-[0_8px_30px_rgb(0,0,0,0.04)]`).
- **Warna Aksen:**
  - Primary / Active: Electric Blue (`#2563eb`) / Deep Sky (`#0284c7`).
  - Positive / Omzet Naik / Lunas: Soft Mint Green (`#10b981`).
  - Danger / Void / Stok Kritis: Soft Coral Red (`#ef4444`).
  - Warning / Menipis: Amber (`#f59e0b`).

---

## 2. Pemisahan Layout Antarmuka

1. **Backoffice Layout (`layouts/app.blade.php`):**
   - Dock rail navigasi samping kiri untuk modul: Dashboard, Produk & Resep (BOM), Inventori & Gudang, Laporan Penjualan, dan AI Consultant.
   - Livewire 3 Components untuk interaksi data reaktif.
2. **POS Cashier Layout (`layouts/pos.blade.php`):**
   - Fullscreen kasir tanpa navbar admin.
   - Kolom kiri: Kategori tab & Grid kartu menu produk.
   - Kolom kanan: Panel Keranjang Pesanan (Alpine.js) + Numpad / Opsi Bayar Tunai & QRIS Dinamis.
   - Fast switch PIN lock pad modal.

