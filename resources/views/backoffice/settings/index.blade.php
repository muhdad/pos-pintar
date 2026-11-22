<x-app-layout>
    @section('page_title', 'Pengaturan Outlet & QRIS')

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="mb-4 p-3.5 bg-emerald-50 border-2 border-emerald-600 text-emerald-900 text-xs font-bold flex items-center justify-between shadow-[2px_2px_0px_#059669]">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 bg-emerald-600 text-white flex items-center justify-center text-xs font-black">✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-800 hover:text-black font-black p-1">✕</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3.5 bg-red-50 border-2 border-[#DC2626] text-red-900 text-xs font-bold shadow-[2px_2px_0px_#DC2626]">
            <p class="font-black uppercase mb-1">Terjadi Kesalahan Validasi:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6" x-data="qrisSettingsManager()">
        <!-- Header Bauhaus -->
        <div class="bg-white border-2 border-[#121212] p-5 shadow-[4px_4px_0px_#121212] flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-3.5 h-3.5 bg-[#DC2626] border border-black inline-block"></span>
                    <h1 class="font-display font-black text-xl text-[#121212] tracking-wider uppercase">
                        PENGATURAN OUTLET & QRIS TOKO
                    </h1>
                </div>
                <p class="text-xs text-stone-600 font-mono mt-1">
                    Konfigurasi profil cabang usaha dan integrasi pembayaran QRIS Dinamis otomatis nominal sesuai standar Bank Indonesia (EMVCo).
                </p>
            </div>
            
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('pos.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#FACC15] hover:bg-yellow-400 text-[#121212] font-display font-black text-xs uppercase border-2 border-[#121212] shadow-[2px_2px_0px_#121212] active:translate-x-0.5 active:translate-y-0.5 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Buka Layar Kasir (F2)</span>
                </a>
            </div>
        </div>

        <form action="{{ route('backoffice.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Kolom Kiri: Form Data Outlet & Payload QRIS (7 Cols) -->
                <div class="lg:col-span-7 space-y-6">
                    <!-- Card 1: Profil Outlet -->
                    <div class="bg-white border-2 border-[#121212] shadow-[4px_4px_0px_#121212] p-5">
                        <div class="flex items-center gap-2 pb-3 mb-4 border-b-2 border-stone-200">
                            <span class="w-3 h-3 bg-[#1D4ED8] border border-black"></span>
                            <h2 class="font-display font-black text-sm uppercase tracking-wide text-[#121212]">
                                1. Identitas & Kontak Cabang
                            </h2>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-black uppercase font-mono text-stone-800 mb-1">
                                    Nama Cabang / Outlet <span class="text-red-600">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       required
                                       value="{{ old('name', $outlet->name ?? 'SLL Cabang Utama') }}" 
                                       placeholder="Contoh: SLL Cabang Utama"
                                       class="w-full bg-[#F6F4ED] border-2 border-[#121212] px-3 py-2 text-xs font-bold text-stone-900 focus:outline-none focus:bg-white focus:shadow-[2px_2px_0px_#121212]">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase font-mono text-stone-800 mb-1">
                                        Nomor Telepon / WhatsApp
                                    </label>
                                    <input type="text" 
                                           name="phone" 
                                           value="{{ old('phone', $outlet->phone ?? '+62 852-1325-4219') }}" 
                                           placeholder="Contoh: +62 852-1325-4219"
                                           class="w-full bg-[#F6F4ED] border-2 border-[#121212] px-3 py-2 text-xs font-mono font-bold text-stone-900 focus:outline-none focus:bg-white focus:shadow-[2px_2px_0px_#121212]">
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase font-mono text-stone-800 mb-1">
                                        Tipe Outlet
                                    </label>
                                    <div class="bg-stone-100 border-2 border-stone-300 px-3 py-2 text-xs font-mono text-stone-600 font-bold">
                                        {{ $outlet->type ?? 'RESTO' }} (Food & Beverage)
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase font-mono text-stone-800 mb-1">
                                    Alamat Lengkap Restoran
                                </label>
                                <textarea name="address" 
                                          rows="2" 
                                          placeholder="Alamat fisik untuk dicetak pada struk kasir thermal..."
                                          class="w-full bg-[#F6F4ED] border-2 border-[#121212] px-3 py-2 text-xs font-medium text-stone-900 focus:outline-none focus:bg-white focus:shadow-[2px_2px_0px_#121212]">{{ old('address', $outlet->address ?? 'Jl. Raya Lamongan No. 12') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Pengaturan QRIS Toko -->
                    <div class="bg-white border-2 border-[#121212] shadow-[4px_4px_0px_#121212] p-5">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b-2 border-stone-200">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-[#DC2626] border border-black"></span>
                                <h2 class="font-display font-black text-sm uppercase tracking-wide text-[#121212]">
                                    2. Pengaturan QRIS Toko (Nominal Otomatis)
                                </h2>
                            </div>
                            <span class="text-[10px] font-black uppercase font-mono bg-[#FACC15] px-2 py-0.5 border border-black">
                                STANDAR ASPI EMVCo
                            </span>
                        </div>

                        <!-- Banner Info Penjelasan -->
                        <div class="bg-[#FAF8F5] border-2 border-stone-800 p-3.5 mb-5 space-y-2 text-xs">
                            <div class="flex items-center gap-2 text-stone-900 font-bold">
                                <span class="text-base">💡</span>
                                <span>Bagaimana QRIS Dinamis Otomatis Nominal Bekerja?</span>
                            </div>
                            <p class="text-stone-600 leading-relaxed text-[11px]">
                                Anda <strong>cukup memasukkan 1 kali</strong> kode string QRIS statis toko (dari stiker QRIS Bank BCA, Mandiri, BRI, BNI, ShopeePay, Gopay, atau Midtrans). 
                                Ketika kasir klik <em>Bayar</em> di POS, sistem secara instan mengubahnya menjadi <strong>QRIS Dinamis</strong> dengan nominal belanjaan terkunci secara otomatis. Pelanggan tinggal scan tanpa repot mengetik angka rupiah lagi!
                            </p>
                        </div>

                        <div class="space-y-4">
                            <!-- String Payload QRIS Statis -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-black uppercase font-mono text-stone-800">
                                        String Payload QRIS Statis Toko
                                    </label>
                                    <button type="button" 
                                            @click="pasteSampleTemplate()"
                                            class="text-[10px] text-[#1D4ED8] hover:underline font-mono font-bold">
                                        + Gunakan Template Default SLL
                                    </button>
                                </div>
                                <textarea name="qris_payload" 
                                          x-model="rawPayload"
                                          @input="updateLivePreview()"
                                          rows="4" 
                                          placeholder="Contoh: 00020101021126580014ID.LINKAJA.WWW01189360091100223405100208123456785204581253033605802ID5922SOTO LAMONGAN LESTARI6007JAKARTA6105123456304ABCD"
                                          class="w-full bg-[#F6F4ED] border-2 border-[#121212] p-2.5 text-[11px] font-mono text-stone-900 focus:outline-none focus:bg-white focus:shadow-[2px_2px_0px_#121212]">{{ old('qris_payload', $outlet->qris_payload) }}</textarea>
                                <p class="text-[10px] text-stone-500 font-mono mt-1">
                                    *Jika dikosongkan, sistem akan menggunakan template default resmi Soto Lamongan Lestari.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase font-mono text-stone-800 mb-1">
                                        Nama Merchant (Tercetak di QR)
                                    </label>
                                    <input type="text" 
                                           name="qris_merchant_name" 
                                           x-model="merchantName"
                                           value="{{ old('qris_merchant_name', $outlet->qris_merchant_name ?? 'SOTO LAMONGAN LESTARI') }}" 
                                           placeholder="SOTO LAMONGAN LESTARI"
                                           class="w-full bg-[#F6F4ED] border-2 border-[#121212] px-3 py-2 text-xs font-bold text-stone-900 uppercase focus:outline-none focus:bg-white focus:shadow-[2px_2px_0px_#121212]">
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase font-mono text-stone-800 mb-1">
                                        Kota Merchant
                                    </label>
                                    <input type="text" 
                                           name="qris_city" 
                                           x-model="merchantCity"
                                           value="{{ old('qris_city', $outlet->qris_city ?? 'JAKARTA') }}" 
                                           placeholder="JAKARTA"
                                           class="w-full bg-[#F6F4ED] border-2 border-[#121212] px-3 py-2 text-xs font-bold text-stone-900 uppercase focus:outline-none focus:bg-white focus:shadow-[2px_2px_0px_#121212]">
                                </div>
                            </div>

                            <!-- Upload Foto/Stiker QRIS -->
                            <div>
                                <label class="block text-xs font-black uppercase font-mono text-stone-800 mb-1">
                                    Upload Foto Stiker QRIS Toko (Opsional / Arsip)
                                </label>
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                    <input type="file" 
                                           name="qris_image" 
                                           accept="image/*"
                                           @change="previewImage($event)"
                                           class="text-xs file:mr-3 file:py-1.5 file:px-3 file:border-2 file:border-black file:bg-[#FACC15] file:text-xs file:font-black file:uppercase file:cursor-pointer hover:file:bg-yellow-400">
                                    
                                    @if(!empty($outlet->qris_image_path))
                                        <a href="{{ asset($outlet->qris_image_path) }}" target="_blank" class="text-[11px] text-[#1D4ED8] hover:underline font-mono font-bold flex items-center gap-1">
                                            <span>📷 Lihat Stiker QRIS Tersimpan</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Panduan 3 Langkah -->
                            <div class="bg-amber-50 border border-amber-300 p-3 text-[11px] text-amber-950 space-y-1">
                                <span class="font-black uppercase tracking-wider block font-mono text-amber-800">
                                    📌 Cara Mudah Mendapatkan String Payload QRIS:
                                </span>
                                <ol class="list-decimal list-inside space-y-1 text-stone-700">
                                    <li>Buka kamera smartphone atau aplikasi <strong>Google Lens / QR Scanner</strong>.</li>
                                    <li>Arahkan ke stiker QRIS toko Anda (BCA / Mandiri / Nobu / dll).</li>
                                    <li>Salin (Copy) teks hasil scan yang berawalan <code class="bg-amber-100 px-1 font-mono text-red-600">000201...</code> lalu paste pada kolom String Payload di atas.</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Tombol Simpan -->
                        <div class="mt-6 pt-4 border-t-2 border-stone-200 flex items-center justify-end">
                            <button type="submit" 
                                    class="px-5 py-2.5 bg-[#DC2626] hover:bg-[#B91C1C] text-white font-display font-black text-xs uppercase tracking-wider border-2 border-[#121212] shadow-[3px_3px_0px_#121212] active:translate-x-0.5 active:translate-y-0.5 transition-all flex items-center gap-2">
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
                    <div class="bg-white border-2 border-[#121212] shadow-[4px_4px_0px_#121212] p-5 sticky top-20">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b-2 border-stone-200">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-[#FACC15] border border-black"></span>
                                <h2 class="font-display font-black text-sm uppercase tracking-wide text-[#121212]">
                                    Live Simulator QRIS Dinamis
                                </h2>
                            </div>
                            <span class="text-[9px] font-mono font-bold text-emerald-700 bg-emerald-100 border border-emerald-400 px-1.5 py-0.5">
                                READY TO SCAN
                            </span>
                        </div>

                        <p class="text-xs text-stone-600 mb-3 leading-relaxed">
                            Ketik nominal belanja uji coba di bawah ini. Kode QRIS di bawah akan seketika berubah menjadi <strong>QRIS Dinamis</strong> ber-nominal otomatis. Silakan coba scan langsung dengan HP Anda!
                        </p>

                        <!-- Input Nominal Uji Coba -->
                        <div class="bg-[#F6F4ED] border-2 border-[#121212] p-3 mb-4 shadow-[2px_2px_0px_#121212]">
                            <label class="block text-[10px] font-black uppercase font-mono text-stone-700 mb-1">
                                Nominal Transaksi Uji Coba:
                            </label>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-xs text-stone-700">Rp</span>
                                <input type="number" 
                                       x-model.number="testAmount" 
                                       @input="updateLivePreview()"
                                       step="1000"
                                       min="1000"
                                       class="w-full bg-white border-2 border-[#121212] px-2.5 py-1.5 text-sm font-mono font-black text-[#121212] focus:outline-none">
                            </div>

                            <div class="flex items-center gap-1.5 mt-2">
                                <span class="text-[9px] font-mono text-stone-500">Cepat:</span>
                                <button type="button" @click="testAmount = 15000; updateLivePreview()" class="text-[10px] font-mono px-2 py-0.5 bg-white border border-stone-400 hover:bg-stone-100 font-bold">15K</button>
                                <button type="button" @click="testAmount = 25000; updateLivePreview()" class="text-[10px] font-mono px-2 py-0.5 bg-white border border-stone-400 hover:bg-stone-100 font-bold">25K</button>
                                <button type="button" @click="testAmount = 50000; updateLivePreview()" class="text-[10px] font-mono px-2 py-0.5 bg-white border border-stone-400 hover:bg-stone-100 font-bold">50K</button>
                                <button type="button" @click="testAmount = 100000; updateLivePreview()" class="text-[10px] font-mono px-2 py-0.5 bg-white border border-stone-400 hover:bg-stone-100 font-bold">100K</button>
                            </div>
                        </div>

                        <!-- Card Preview QRIS Resmi -->
                        <div class="bg-[#FAF8F5] border-2 border-[#121212] p-4 text-center shadow-[3px_3px_0px_#121212]">
                            <!-- Header Logo QRIS -->
                            <div class="flex items-center justify-between pb-2 mb-2 border-b-2 border-stone-900">
                                <span class="bg-[#DC2626] text-white px-2 py-0.5 text-[10px] font-black tracking-widest font-mono">
                                    QRIS
                                </span>
                                <span class="text-[9px] font-mono font-bold text-stone-600">
                                    PEMBAYARAN DIGITAL
                                </span>
                            </div>

                            <div class="text-xs font-black uppercase tracking-wider text-stone-900 truncate" x-text="merchantName || '{{ $outlet->name ?? 'SOTO LAMONGAN LESTARI' }}'"></div>
                            <div class="text-[10px] font-mono text-stone-500 mb-2 truncate" x-text="merchantCity || 'JAKARTA'"></div>

                            <!-- Canvas QR Code -->
                            <div class="p-3 bg-white border-2 border-stone-900 inline-block shadow-[2px_2px_0px_#121212] my-1">
                                <canvas id="qris-settings-canvas" class="mx-auto block"></canvas>
                            </div>

                            <!-- Display Nominal Terkunci -->
                            <div class="mt-2.5 pt-2 border-t-2 border-stone-200">
                                <span class="text-[10px] uppercase font-mono font-bold text-stone-500 block">Nominal Terkunci Otomatis:</span>
                                <div class="text-lg font-display font-black text-[#DC2626] font-mono tracking-tight">
                                    Rp<span x-text="Number(testAmount || 0).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Diagnostic String -->
                        <div class="mt-4 bg-[#121212] text-stone-200 p-3 font-mono text-[9px] border-2 border-[#121212] space-y-1">
                            <div class="flex justify-between text-stone-400">
                                <span>STATUS PAYLOAD:</span>
                                <span class="text-emerald-400 font-bold">VALID EMVCo DYNAMIC</span>
                            </div>
                            <div class="flex justify-between text-stone-400">
                                <span>TAG 01 (POINT OF INITIATION):</span>
                                <span class="text-[#FACC15] font-bold">12 (DYNAMIC / NOMINAL)</span>
                            </div>
                            <div class="flex justify-between text-stone-400">
                                <span>TAG 54 (NOMINAL AMOUNT):</span>
                                <span class="text-white font-bold" x-text="`54${String(String(Math.round(testAmount)).length).padStart(2, '0')}${Math.round(testAmount)}`"></span>
                            </div>
                            <div class="truncate text-stone-500 pt-1 border-t border-stone-800" :title="generatedDynamicString">
                                <span class="text-stone-400">STRING: </span>
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
