# Offline-First Sync Architecture (Alpine.js + Dexie.js)

Dokumen ini menjelaskan strategi kasir *Offline-First* di POS Pintar.

---

## 1. Filosofi: Kasir Tidak Pernah Berhenti (0ms Latency)

- Setiap klik item, penambahan modifier, perhitungan diskon dan pajak diproses langsung di browser client via Alpine.js tanpa request Livewire bolak-balik.
- Data master produk dan kategori disimpan di IndexedDB melalui **Dexie.js**.
- Saat transaksi selesai (Tunai / EDC / Transfer), order disimpan ke tabel lokal `orders` dan antrean sync `syncQueue`.
- Stok lokal di `stocks` dipotong secara optimis di browser.
- Struk langsung dicetak ke printer thermal Bluetooth / USB menggunakan WebUSB / ESC/POS.

---

## 2. Alur Sinkronisasi ke Laravel Cloud Backend

1. **Pemeriksaan Koneksi:** Menggunakan listener `navigator.onLine` dan heartbeat periodik.
2. **Pengiriman Antrean (Sync Worker):**
   - Saat online, worker membaca `syncQueue` di Dexie.js.
   - Mengirim batch payload ke endpoint API Laravel: `POST /api/pos/sync`.
3. **Pemrosesan Idempoten di Laravel (`OrderService`):**
   - Laravel memeriksa apakah UUID order sudah pernah tersimpan (`Order::find($payload['id'])`).
   - Jika belum, order disimpan beserta `order_items` (snapshot `unit_price` dan `unit_cost`), memotong stok riil di database via `InventoryDeductionService`, dan mencatat `payments`.
   - Mengembalikan response `HTTP 200 { success: true, synced_ids: [...] }`.
4. **Pembersihan Antrean Lokal:**
   - Dexie.js menandai order lokal sebagai `SYNCED` dan menghapus item dari `syncQueue`.

