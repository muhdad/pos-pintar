# AI Engine & Token Optimization Architecture

Dokumen ini mendefinisikan integrasi kecerdasan buatan (AI) di aplikasi POS Pintar berbasis Laravel.

---

## 1. Efisiensi Token & Scheduled Aggregator (Sesuai AGENTS.md Rule 4)

Mengirim seluruh data row transaksi ke LLM API akan sangat boros token dan lambat. Oleh karena itu:
1. **`BusinessDataAggregator.php`:**
   - Menggunakan Laravel Eloquent Query Builder untuk mengagregasi data transaksi harian menjadi JSON ringkas (omzet, total HPP, margin kotor, menu terlaris, dead-stock, bahan baku di bawah batas aman).
2. **`php artisan app:generate-ai-insights`:**
   - Dijalankan via Laravel Task Scheduler setiap malam setelah shift kasir tutup.
   - Mengirim rangkuman JSON ke Google Gemini API dengan response berformat JSON Schema terstruktur.
   - Menyimpan hasil analisis taktis ke tabel `ai_daily_reports`.
3. **`AiConsultantService.php`:**
   - Menyediakan interface tanya-jawab untuk Owner di Backoffice Livewire.
   - Menganalisis rekomendasi bundling menu, strategi promo, dan peringatan potensi kecurangan (fraud alert).

