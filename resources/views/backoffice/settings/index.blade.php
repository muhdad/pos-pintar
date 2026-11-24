<x-app-layout>
    @section('page_title', 'Pengaturan Outlet & QRIS')

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="mb-4 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-bold">✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold p-1">✕</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-2xl shadow-xs">
            <p class="font-bold uppercase mb-1">Terjadi Kesalahan Validasi:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6" x-data="qrisSettingsManager()">
        <!-- Standard Header -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h1 class="font-bold text-lg sm:text-xl text-slate-800 tracking-tight">
                        Pengaturan Outlet & QRIS Toko
                    </h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Konfigurasi profil cabang usaha dan integrasi pembayaran QRIS Dinamis otomatis nominal sesuai standar Bank Indonesia (EMVCo).
                </p>
            </div>
            
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('pos.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition active:translate-y-px">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Buka Layar Kasir</span>
                </a>
            </div>
        </div>

        <form action="{{ route('backoffice.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Kolom Kiri: Form Data Outlet & Payload QRIS (7 Cols) -->
                <div class="lg:col-span-7 space-y-6">
                    <!-- Card 1: Profil Outlet -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <h2 class="font-bold text-sm text-slate-800">
                                1. Identitas & Kontak Cabang
                            </h2>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Nama Cabang / Outlet <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       required
                                       value="{{ old('name', $outlet->name ?? 'SLL Cabang Utama') }}" 
                                       placeholder="Contoh: SLL Cabang Utama"
                                       class="w-full bg-slate-50/60 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Nomor Telepon / WhatsApp
                                    </label>
                                    <input type="text" 
                                           name="phone" 
                                           value="{{ old('phone', $outlet->phone ?? '+62 852-1325-4219') }}" 
                                           placeholder="Contoh: +62 852-1325-4219"
                                           class="w-full bg-slate-50/60 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Tipe Outlet
                                    </label>
                                    <div class="bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono text-slate-600 font-semibold">
                                        {{ $outlet->type ?? 'RESTO' }} (Food & Beverage)
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Alamat Lengkap Restoran
                                </label>
                                <textarea name="address" 
                                          rows="2" 
                                          placeholder="Alamat fisik untuk dicetak pada struk kasir thermal..."
                                          class="w-full bg-slate-50/60 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition">{{ old('address', $outlet->address ?? 'Jl. Raya Lamongan No. 12') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Pengaturan QRIS Toko -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <h2 class="font-bold text-sm text-slate-800">
                                    2. Pengaturan QRIS Toko (Nominal Otomatis)
                                </h2>
                            </div>
                            <span class="text-[10px] font-semibold uppercase font-mono bg-emerald-50 text-emerald-700 px-2.5 py-0.5 rounded-lg border border-emerald-200">
                                Standar EMVCo MPM
                            </span>
                        </div>

                        <!-- Banner Info Penjelasan -->
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4 mb-5 space-y-2 text-xs">
                            <div class="flex items-center gap-2 text-slate-800 font-bold">
                                <span>💡</span>
                                <span>Bagaimana QRIS Dinamis Otomatis Nominal Bekerja?</span>
                            </div>
                            <p class="text-slate-600 leading-relaxed text-[11px]">
                                Anda <strong>cukup memasukkan 1 kali</strong> kode string QRIS statis toko (dari stiker QRIS Bank BCA, Mandiri, BRI, BNI, ShopeePay, Gopay, atau Midtrans). 
                                Ketika kasir klik <em>Bayar</em> di POS, sistem secara instan mengubahnya menjadi <strong>QRIS Dinamis</strong> dengan nominal belanjaan terkunci secara otomatis. Pelanggan tinggal scan tanpa repot mengetik angka rupiah lagi!
                            </p>
                        </div>

                        <div class="space-y-4">
                            <!-- String Payload QRIS Statis -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-700">
                                        String Payload QRIS Statis Toko
                                    </label>
                                    <button type="button" 
                                            @click="pasteSampleTemplate()"
                                            class="text-[10px] text-emerald-700 hover:underline font-mono font-semibold">
                                        + Gunakan Template Default SLL
                                    </button>
                                </div>
                                <textarea name="qris_payload" 
                                          x-model="rawPayload"
                                          @input="updateLivePreview()"
                                          rows="4" 
                                          placeholder="Contoh: 00020101021126580014ID.LINKAJA.WWW01189360091100223405100208123456785204581253033605802ID5922SOTO LAMONGAN LESTARI6007JAKARTA6105123456304ABCD"
                                          class="w-full bg-slate-50/60 border border-slate-200 rounded-xl p-3 text-[11px] font-mono text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition">{{ old('qris_payload', $outlet->qris_payload) }}</textarea>
                                <p class="text-[10px] text-slate-400 font-mono mt-1">
                                    *Jika dikosongkan, sistem akan menggunakan template default resmi Soto Lamongan Lestari.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Nama Merchant (Tercetak di QR)
                                    </label>
                                    <input type="text" 
                                           name="qris_merchant_name" 
                                           x-model="merchantName"
                                           value="{{ old('qris_merchant_name', $outlet->qris_merchant_name ?? 'SOTO LAMONGAN LESTARI') }}" 
                                           placeholder="SOTO LAMONGAN LESTARI"
                                           class="w-full bg-slate-50/60 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                                        Kota Merchant
                                    </label>
                                    <input type="text" 
                                           name="qris_city" 
                                           x-model="merchantCity"
                                           value="{{ old('qris_city', $outlet->qris_city ?? 'JAKARTA') }}" 
                                           placeholder="JAKARTA"
                                           class="w-full bg-slate-50/60 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition">
                                </div>
                            </div>

                            <!-- Upload Foto/Stiker QRIS -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    Upload Foto Stiker QRIS Toko (Opsional / Arsip)
                                </label>
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                    <input type="file" 
                                           name="qris_image" 
                                           accept="image/*"
                                           @change="previewImage($event)"
                                           class="text-xs file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border file:border-slate-200 file:bg-slate-50 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-100">
                                    
                                    @if(!empty($outlet->qris_image_path))
                                        <a href="{{ asset($outlet->qris_image_path) }}" target="_blank" class="text-[11px] text-emerald-700 hover:underline font-mono font-semibold flex items-center gap-1">
                                            <span>📷 Lihat Stiker QRIS Tersimpan</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Panduan 3 Langkah -->
                            <div class="bg-amber-50/80 rounded-2xl border border-amber-200/80 p-3.5 text-[11px] text-amber-950 space-y-1">
                                <span class="font-bold tracking-wide block text-amber-800">
                                    📌 Cara Mudah Mendapatkan String Payload QRIS:
                                </span>
                                <ol class="list-decimal list-inside space-y-1 text-slate-700">
                                    <li>Buka kamera smartphone atau aplikasi <strong>Google Lens / QR Scanner</strong>.</li>
                                    <li>Arahkan ke stiker QRIS toko Anda (BCA / Mandiri / Nobu / ShopeePay / dll).</li>
                                    <li>Salin (Copy) teks hasil scan yang berawalan <code class="bg-amber-100 px-1 font-mono text-rose-600 rounded">000201...</code> lalu paste pada kolom String Payload di atas.</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Tombol Simpan -->
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end">
                            <button type="submit" 
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition active:translate-y-px flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Simpan Pengaturan Outlet & QRIS</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Live Interactive QRIS Simulator (5 Cols) -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sticky top-20">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <h2 class="font-bold text-sm text-slate-800">
                                    Live Simulator QRIS Dinamis
                                </h2>
                            </div>
                            <span class="text-[9px] font-mono font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">
                                Ready to Scan
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                            Ketik nominal belanja uji coba di bawah ini. Kode QRIS di bawah akan seketika berubah menjadi <strong>QRIS Dinamis</strong> ber-nominal otomatis. Silakan coba scan langsung dengan HP Anda!
                        </p>

                        <!-- Input Nominal Uji Coba -->
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-3.5 mb-4">
                            <label class="block text-[10px] font-bold uppercase font-mono text-slate-500 mb-1">
                                Nominal Transaksi Uji Coba:
                            </label>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-xs text-slate-500">Rp</span>
                                <input type="number" 
                                       x-model.number="testAmount" 
                                       @input="updateLivePreview()"
                                       step="1000"
                                       min="1000"
                                       class="w-full bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400">
                            </div>

                            <div class="flex items-center gap-1.5 mt-2.5">
                                <span class="text-[10px] font-mono text-slate-400">Cepat:</span>
                                <button type="button" @click="testAmount = 15000; updateLivePreview()" class="text-[10px] font-mono px-2 py-0.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-semibold text-slate-700">15K</button>
                                <button type="button" @click="testAmount = 25000; updateLivePreview()" class="text-[10px] font-mono px-2 py-0.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-semibold text-slate-700">25K</button>
                                <button type="button" @click="testAmount = 50000; updateLivePreview()" class="text-[10px] font-mono px-2 py-0.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-semibold text-slate-700">50K</button>
                                <button type="button" @click="testAmount = 100000; updateLivePreview()" class="text-[10px] font-mono px-2 py-0.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-semibold text-slate-700">100K</button>
                            </div>
                        </div>

                        <!-- Card Preview QRIS Resmi -->
                        <div class="bg-slate-50/80 rounded-2xl border border-slate-200 p-4 text-center">
                            <!-- Header Logo QRIS -->
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200">
                                <div class="flex items-center gap-1.5">
                                    <span class="bg-rose-600 text-white px-2 py-0.5 rounded text-[10px] font-bold font-mono">
                                        QRIS
                                    </span>
                                    <span class="bg-indigo-600 text-white px-1.5 py-0.5 rounded text-[9px] font-bold font-mono">
                                        GPN
                                    </span>
                                </div>
                                <span class="text-[10px] font-mono font-medium text-slate-500">
                                    PEMBAYARAN DIGITAL
                                </span>
                            </div>

                            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide truncate" x-text="merchantName || '{{ $outlet->name ?? 'SOTO LAMONGAN LESTARI' }}'"></div>
                            <div class="text-[10px] font-mono text-slate-400 mb-2 truncate" x-text="merchantCity || 'JAKARTA'"></div>

                            <!-- Canvas QR Code -->
                            <div class="p-3 bg-white border border-slate-200 rounded-2xl shadow-xs inline-block my-1">
                                <canvas id="qris-settings-canvas" class="mx-auto block"></canvas>
                            </div>

                            <!-- Display Nominal Terkunci -->
                            <div class="mt-2.5 pt-2 border-t border-slate-200">
                                <span class="text-[10px] uppercase font-mono font-semibold text-slate-400 block">Nominal Terkunci Otomatis:</span>
                                <div class="text-lg font-bold text-emerald-700 font-mono tracking-tight">
                                    Rp<span x-text="Number(testAmount || 0).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Diagnostic String -->
                        <div class="mt-4 bg-[#0F172A] rounded-2xl text-slate-200 p-4 font-mono text-[9px] border border-slate-800 space-y-1.5">
                            <div class="flex justify-between text-slate-400">
                                <span>STATUS PAYLOAD:</span>
                                <span class="text-emerald-400 font-bold">VALID EMVCo DYNAMIC</span>
                            </div>
                            <div class="flex justify-between text-slate-400">
                                <span>TAG 01 (POINT OF INITIATION):</span>
                                <span class="text-amber-300 font-bold">12 (DYNAMIC / NOMINAL)</span>
                            </div>
                            <div class="flex justify-between text-slate-400">
                                <span>TAG 54 (NOMINAL AMOUNT):</span>
                                <span class="text-white font-bold" x-text="`54${String(String(Math.round(testAmount)).length).padStart(2, '0')}${Math.round(testAmount)}`"></span>
                            </div>
                            <div class="truncate text-slate-400 pt-1.5 border-t border-slate-800" :title="generatedDynamicString">
                                <span class="text-slate-500">STRING: </span>
                                <span x-text="generatedDynamicString || '-'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Script Logika Interactive Simulator -->
    <script>
        function qrisSettingsManager() {
            return {
                rawPayload: @json($outlet->qris_payload ?? ''),
                merchantName: @json($outlet->qris_merchant_name ?? $outlet->name ?? 'SOTO LAMONGAN LESTARI'),
                merchantCity: @json($outlet->qris_city ?? 'JAKARTA'),
                testAmount: 15000,
                generatedDynamicString: '',

                init() {
                    if (!this.rawPayload) {
                        this.rawPayload = window.QRIS_HELPER?.DEFAULT_STATIC_QRIS || '';
                    }
                    this.$nextTick(() => {
                        this.updateLivePreview();
                    });
                },

                pasteSampleTemplate() {
                    this.rawPayload = window.QRIS_HELPER?.DEFAULT_STATIC_QRIS || '';
                    this.updateLivePreview();
                },

                updateLivePreview() {
                    const canvas = document.getElementById('qris-settings-canvas');
                    const basePayload = (this.rawPayload && this.rawPayload.trim()) 
                        ? this.rawPayload.trim() 
                        : (window.QRIS_HELPER?.DEFAULT_STATIC_QRIS || '');

                    if (window.QRIS_HELPER) {
                        this.generatedDynamicString = window.QRIS_HELPER.convertToDynamicQris(basePayload, this.testAmount);
                        if (canvas && this.generatedDynamicString) {
                            window.QRIS_HELPER.renderQrisCanvas(canvas, this.generatedDynamicString);
                        }
                    }
                },

                previewImage(event) {
                    const input = event.target;
                    if (input.files && input.files[0]) {
                        // File terpilih
                    }
                }
            };
        }
    </script>
</x-app-layout>
