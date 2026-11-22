<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Customer Display - Soto Lamongan Lestari</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#F6F4ED] text-[#121212] select-none overflow-hidden"
      x-data="customerDisplay()"
      x-init="init()">

    <div class="h-screen w-screen flex flex-col">
        <!-- Top Bauhaus Header -->
        <header class="h-16 bg-[#121212] border-b-2 border-stone-900 px-6 flex items-center justify-between text-white shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#1D4ED8] border-2 border-[#FACC15] text-[#FACC15] flex items-center justify-center font-display font-black text-base shadow-[2px_2px_0px_#DC2626]">
                    SLL
                </div>
                <div>
                    <h1 class="text-base font-display font-black tracking-widest uppercase">SOTO LAMONGAN LESTARI</h1>
                    <span class="text-xs text-[#FACC15] font-mono font-bold tracking-wider">LAYAR PELANGGAN (CUSTOMER DISPLAY)</span>
                </div>
            </div>
            <div class="flex items-center gap-3 font-mono text-xs">
                <span class="px-2.5 py-1 bg-stone-900 border border-stone-700 text-stone-300">
                    {{ $outlet->name ?? 'Cabang Utama' }}
                </span>
                <span class="px-2 py-1 bg-emerald-950 border border-emerald-600 text-emerald-400 font-bold uppercase">
                    🟢 ONLINE
                </span>
            </div>
        </header>

        <!-- Main Display Content Area -->
        <main class="flex-1 flex p-6 gap-6 overflow-hidden">
            <!-- Left Side: Order Items List (Active Cart) -->
            <div class="flex-1 bauhaus-card p-5 bg-white flex flex-col justify-between overflow-hidden">
                <div>
                    <div class="flex items-center justify-between border-b-2 border-[#121212] pb-3 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-[#1D4ED8] border border-black"></span>
                            <h2 class="text-sm font-display font-black uppercase tracking-wider text-[#121212]">
                                Rincian Pesanan Anda
                            </h2>
                        </div>
                        <span class="bauhaus-badge bg-stone-100 text-stone-800 font-mono" x-text="items.length + ' ITEM'"></span>
                    </div>

                    <!-- Items Scrollable Table -->
                    <div class="overflow-y-auto max-h-[55vh] pr-2 space-y-2">
                        <template x-if="items.length === 0">
                            <div class="py-16 text-center text-stone-400 font-mono text-xs">
                                <span class="text-3xl block mb-2">🍜</span>
                                Selamat Datang! Kasir sedang menyiapkan pesanan Anda...
                            </div>
                        </template>

                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="p-3 border-2 border-stone-200 bg-stone-50/70 flex items-center justify-between font-mono text-xs">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-stone-900 text-sm" x-text="item.name"></span>
                                        <span class="px-1.5 py-0.2 bg-[#FACC15] text-[#121212] font-black border border-black text-[10px]" x-text="'x' + item.quantity"></span>
                                    </div>
                                    <template x-if="item.notes">
                                        <span class="text-[10px] text-stone-500 italic block mt-0.5" x-text="'Catatan: ' + item.notes"></span>
                                    </template>
                                </div>
                                <div class="text-right">
                                    <span class="font-black text-stone-900 text-sm" x-text="'Rp ' + Number(item.subtotal || (item.unit_price * item.quantity)).toLocaleString('id-ID')"></span>
                                    <span class="text-[10px] text-stone-400 block" x-text="'@ Rp ' + Number(item.unit_price).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Total Amount Summary Box -->
                <div class="border-t-2 border-[#121212] pt-4 mt-2">
                    <div class="flex justify-between items-center text-xs font-mono text-stone-600 mb-1" x-show="discount > 0">
                        <span>Diskon:</span>
                        <span class="text-red-600 font-bold" x-text="'- Rp ' + Number(discount).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-mono text-stone-600 mb-1" x-show="taxAmount > 0">
                        <span>PPN:</span>
                        <span class="font-bold" x-text="'+ Rp ' + Number(taxAmount).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="p-3 bg-[#121212] text-white border-2 border-[#121212] shadow-[3px_3px_0px_#FACC15] flex justify-between items-center">
                        <div>
                            <span class="text-[9px] font-black text-[#FACC15] uppercase tracking-widest font-mono block">TOTAL PEMBAYARAN</span>
                            <span class="text-xs text-stone-300 font-mono" x-text="orderType || 'DINE IN'"></span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-display font-black text-[#FACC15] font-mono tracking-tight">
                            Rp <span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Dynamic State (Idle / QRIS Dinamis / Success) -->
            <div class="w-full max-w-md bauhaus-card p-6 bg-white flex flex-col justify-center items-center text-center relative overflow-hidden">
                <!-- State 1: QRIS DINAMIS MODE -->
                <div x-show="mode === 'QRIS'" class="space-y-4 w-full">
                    <div class="flex items-center justify-between border-b-2 border-stone-800 pb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="bg-[#DC2626] text-white px-2 py-0.5 text-xs font-black tracking-widest font-mono">QRIS</span>
                            <span class="bg-[#1D4ED8] text-white px-1.5 py-0.5 text-[10px] font-black font-mono">GPN</span>
                        </div>
                        <span class="bauhaus-badge bg-emerald-100 text-emerald-950 border-emerald-600">
                            🟢 NOMINAL OTOMATIS
                        </span>
                    </div>

                    <div>
                        <span class="text-xs font-black uppercase tracking-wider text-stone-600 font-mono block">SOTO LAMONGAN LESTARI</span>
                        <div class="text-3xl sm:text-4xl font-display font-black text-[#121212] font-mono tracking-tight my-1">
                            Rp <span x-text="Number(qrisAmount).toLocaleString('id-ID')"></span>
                        </div>
                        <p class="text-xs text-stone-600 font-medium">
                            Scan QR di bawah ini dengan aplikasi m-Banking atau e-Wallet Anda.
                        </p>
                    </div>

                    <!-- Canvas QRIS -->
                    <div class="p-3 bg-white border-2 border-[#121212] shadow-[3px_3px_0px_#121212] inline-block mx-auto">
                        <canvas id="customer-qris-canvas" class="mx-auto block"></canvas>
                        <div class="mt-2 pt-1.5 border-t border-stone-200 text-[11px] font-mono text-stone-500 flex justify-between">
                            <span>Berlaku: <strong class="text-red-600" x-text="timerFormatted"></strong></span>
                            <span class="text-emerald-700 font-bold">✓ Siap Scan</span>
                        </div>
                    </div>

                    <!-- Supported Apps Strip -->
                    <div class="p-2.5 bg-stone-50 border border-stone-200 rounded-[4px] text-[10px] font-mono text-stone-600 space-y-1">
                        <span class="font-bold text-stone-800 block">Mendukung Seluruh Aplikasi Pembayaran:</span>
                        <span class="block text-stone-500">BCA • Livin Mandiri • BRImo • BNI • GoPay • OVO • ShopeePay • DANA • LinkAja</span>
                    </div>
                </div>

                <!-- State 2: IDLE MODE -->
                <div x-show="mode === 'IDLE'" class="space-y-4 py-12">
                    <div class="w-20 h-20 bg-[#1D4ED8] border-2 border-[#121212] text-[#FACC15] flex items-center justify-center font-display font-black text-3xl shadow-[4px_4px_0px_#DC2626] mx-auto">
                        SLL
                    </div>
                    <div>
                        <h3 class="text-xl font-display font-black uppercase text-[#121212] tracking-wide">
                            Soto Lamongan Lestari
                        </h3>
                        <p class="text-xs text-stone-600 font-mono mt-1">
                            Cita Rasa Tradisional Lamongan Asli
                        </p>
                    </div>
                    <div class="p-3 bg-amber-50 border-2 border-stone-800 text-xs font-mono text-amber-950">
                        Nikmati pembayaran praktis & higienis dengan QRIS Dinamis.
                    </div>
                </div>

                <!-- State 3: PAYMENT SUCCESS -->
                <div x-show="mode === 'SUCCESS'" class="space-y-4 py-8 animate-in zoom-in">
                    <div class="w-16 h-16 bg-emerald-500 border-2 border-black text-white flex items-center justify-center text-3xl font-black shadow-[3px_3px_0px_#121212] mx-auto">
                        ✓
                    </div>
                    <div>
                        <h3 class="text-2xl font-display font-black uppercase text-emerald-800 tracking-wide">
                            Pembayaran Berhasil!
                        </h3>
                        <span class="text-xs text-stone-600 font-mono block mt-1">Terima kasih atas kunjungan Anda di SLL</span>
                    </div>
                    <div class="p-4 bg-emerald-50 border-2 border-emerald-700 text-emerald-950 font-mono text-xs space-y-1">
                        <div class="flex justify-between">
                            <span>Status:</span>
                            <span class="font-bold">LUNAS</span>
                        </div>
                        <template x-if="successOrder">
                            <div class="flex justify-between">
                                <span>No. Struk:</span>
                                <span class="font-bold" x-text="successOrder.order_number"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function customerDisplay() {
            return {
                mode: 'IDLE', // IDLE, QRIS, SUCCESS
                items: [],
                subtotal: 0,
                taxAmount: 0,
                discount: 0,
                finalTotal: 0,
                orderType: 'DINE IN',
                qrisAmount: 0,
                qrisString: '',
                timerSeconds: 300,
                timerInterval: null,
                successOrder: null,

                init() {
                    // 1. Dengarkan pesan dari BroadcastChannel
                    let channel = null;
                    try {
                        channel = new BroadcastChannel('sll_pos_customer_display');
                        channel.onmessage = (event) => {
                            this.handleMessage(event.data);
                        };
                    } catch (e) {
                        console.warn('BroadcastChannel tidak aktif:', e);
                    }

                    // 2. Dengarkan localStorage event sebagai fallback
                    window.addEventListener('storage', (e) => {
                        if (e.key === 'sll_pos_customer_display_event' && e.newValue) {
                            try {
                                const data = JSON.parse(e.newValue);
                                this.handleMessage(data);
                            } catch (err) {}
                        }
                    });
                },

                handleMessage(data) {
                    if (!data) return;

                    if (data.type === 'CART_UPDATED') {
                        this.items = data.items || [];
                        this.subtotal = data.subtotal || 0;
                        this.taxAmount = data.taxAmount || 0;
                        this.discount = data.discount || 0;
                        this.finalTotal = data.finalTotal || 0;
                        this.orderType = data.orderType || 'DINE IN';
                        if (this.mode !== 'QRIS') {
                            this.mode = this.items.length > 0 ? 'IDLE' : 'IDLE';
                        }
                    } else if (data.type === 'SHOW_QRIS') {
                        this.items = data.items || this.items;
                        this.finalTotal = data.finalTotal || this.finalTotal;
                        this.qrisAmount = data.amount || this.finalTotal;
                        this.qrisString = data.dynamicQris || '';
                        this.mode = 'QRIS';

                        this.timerSeconds = 300;
                        if (this.timerInterval) clearInterval(this.timerInterval);
                        this.timerInterval = setInterval(() => {
                            if (this.timerSeconds > 0) {
                                this.timerSeconds--;
                            } else {
                                clearInterval(this.timerInterval);
                            }
                        }, 1000);

                        this.$nextTick(() => {
                            const canvas = document.getElementById('customer-qris-canvas');
                            if (canvas && this.qrisString && window.QRIS_HELPER) {
                                window.QRIS_HELPER.renderQrisCanvas(canvas, this.qrisString);
                            }
                        });
                    } else if (data.type === 'PAYMENT_SUCCESS') {
                        this.successOrder = data.order;
                        this.mode = 'SUCCESS';
                        setTimeout(() => {
                            this.mode = 'IDLE';
                            this.items = [];
                            this.finalTotal = 0;
                        }, 8000);
                    }
                },

                get timerFormatted() {
                    const mins = Math.floor(this.timerSeconds / 60);
                    const secs = this.timerSeconds % 60;
                    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                }
            };
        }
    </script>
</body>
</html>
