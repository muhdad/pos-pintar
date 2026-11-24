<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Customer Display - Soto Lamongan Lestari</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#F8FAFC] text-slate-800 select-none overflow-hidden"
      x-data="customerDisplay()"
      x-init="init()">

    <div class="h-screen w-screen flex flex-col">
        <!-- Top Modern Slate Header -->
        <header class="h-14 bg-[#0F172A] border-b border-slate-800 px-6 flex items-center justify-between text-white shrink-0 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    🍜
                </div>
                <div>
                    <h1 class="text-sm font-bold tracking-wide uppercase">SOTO LAMONGAN LESTARI</h1>
                    <span class="text-[11px] text-emerald-300 font-mono font-medium">Layar Pelanggan (Customer Display)</span>
                </div>
            </div>
            <div class="flex items-center gap-2.5 font-mono text-xs">
                <span class="px-3 py-1 bg-slate-800 border border-slate-700 text-slate-300 rounded-xl">
                    {{ $outlet->name ?? 'Cabang Utama' }}
                </span>
                <span class="px-2.5 py-1 bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 font-semibold rounded-xl">
                    🟢 ONLINE
                </span>
            </div>
        </header>

        <!-- Main Display Content Area -->
        <main class="flex-1 flex p-6 gap-6 overflow-hidden">
            <!-- Left Side: Order Items List (Active Cart) -->
            <div class="flex-1 bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between overflow-hidden">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-800">
                                Rincian Pesanan Anda
                            </h2>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-600 font-mono text-xs font-semibold" x-text="items.length + ' Item'"></span>
                    </div>

                    <!-- Items Scrollable Table -->
                    <div class="overflow-y-auto max-h-[55vh] pr-2 space-y-2">
                        <template x-if="items.length === 0">
                            <div class="py-20 text-center text-slate-400 font-mono text-xs">
                                <span class="text-4xl block mb-2">🍜</span>
                                Selamat Datang! Kasir sedang menyiapkan pesanan Anda...
                            </div>
                        </template>

                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="p-3.5 rounded-2xl border border-slate-100 bg-slate-50/70 flex items-center justify-between font-mono text-xs">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-800 text-sm font-sans" x-text="item.name"></span>
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 rounded-lg text-[10px]" x-text="'x' + item.quantity"></span>
                                    </div>
                                    <template x-if="item.notes">
                                        <span class="text-[10px] text-slate-400 italic block mt-0.5" x-text="'Catatan: ' + item.notes"></span>
                                    </template>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-slate-800 text-sm" x-text="'Rp ' + Number(item.subtotal || (item.unit_price * item.quantity)).toLocaleString('id-ID')"></span>
                                    <span class="text-[10px] text-slate-400 block" x-text="'@ Rp ' + Number(item.unit_price).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Total Amount Summary Box -->
                <div class="border-t border-slate-100 pt-4 mt-2">
                    <div class="flex justify-between items-center text-xs font-mono text-slate-500 mb-1" x-show="discount > 0">
                        <span>Diskon:</span>
                        <span class="text-rose-600 font-bold" x-text="'- Rp ' + Number(discount).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-xs flex justify-between items-center">
                        <div>
                            <span class="text-[10px] font-semibold text-slate-300 uppercase tracking-widest font-mono block">TOTAL TAGIHAN</span>
                            <span class="text-xs text-slate-400 font-mono" x-text="orderType || 'DINE IN'"></span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-bold text-emerald-400 font-mono tracking-tight">
                            Rp <span x-text="Number(finalTotal).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Dynamic State (Idle / QRIS Dinamis / Success) -->
            <div class="w-full max-w-md bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-center items-center text-center relative overflow-hidden">
                <!-- State 1: QRIS DINAMIS MODE -->
                <div x-show="mode === 'QRIS'" class="space-y-4 w-full">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-1.5">
                            <span class="bg-rose-600 text-white px-2 py-0.5 rounded text-xs font-bold font-mono">QRIS</span>
                            <span class="bg-indigo-600 text-white px-1.5 py-0.5 rounded text-[10px] font-bold font-mono">GPN</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono text-[10px] font-semibold">
                            🟢 Nominal Otomatis
                        </span>
                    </div>

                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 font-mono block">SOTO LAMONGAN LESTARI</span>
                        <div class="text-3xl sm:text-4xl font-bold text-slate-800 font-mono tracking-tight my-1">
                            Rp <span x-text="Number(qrisAmount).toLocaleString('id-ID')"></span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">
                            Scan QR di bawah ini dengan aplikasi m-Banking atau e-Wallet Anda.
                        </p>
                    </div>

                    <!-- Canvas QRIS -->
                    <div class="p-3 bg-white border border-slate-200 rounded-2xl shadow-xs inline-block mx-auto">
                        <canvas id="customer-qris-canvas" class="mx-auto block"></canvas>
                        <div class="mt-2.5 pt-1.5 border-t border-slate-100 text-[11px] font-mono text-slate-400 flex justify-between">
                            <span>Berlaku: <strong class="text-rose-500" x-text="timerFormatted"></strong></span>
                            <span class="text-emerald-700 font-semibold">✓ Siap Scan</span>
                        </div>
                    </div>

                    <!-- Supported Apps Strip -->
                    <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-mono text-slate-500 space-y-1">
                        <span class="font-bold text-slate-700 block">Mendukung Seluruh Aplikasi Pembayaran:</span>
                        <span class="block text-slate-400">BCA • Livin Mandiri • BRImo • BNI • GoPay • OVO • ShopeePay • DANA</span>
                    </div>
                </div>

                <!-- State 2: IDLE MODE -->
                <div x-show="mode === 'IDLE'" class="space-y-4 py-12">
                    <div class="w-20 h-20 bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-3xl flex items-center justify-center font-bold text-4xl shadow-md mx-auto">
                        🍜
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 tracking-tight">
                            Soto Lamongan Lestari
                        </h3>
                        <p class="text-xs text-slate-400 font-mono mt-1">
                            Cita Rasa Tradisional Lamongan Asli
                        </p>
                    </div>
                    <div class="p-3.5 bg-emerald-50/80 rounded-2xl border border-emerald-100 text-xs font-mono text-emerald-800">
                        Pembayaran praktis & higienis dengan QRIS Dinamis Otomatis.
                    </div>
                </div>

                <!-- State 3: PAYMENT SUCCESS -->
                <div x-show="mode === 'SUCCESS'" class="space-y-4 py-8 animate-in zoom-in">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center text-3xl font-bold mx-auto">
                        ✓
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 tracking-tight">
                            Pembayaran Berhasil!
                        </h3>
                        <span class="text-xs text-slate-400 font-mono block mt-1">Terima kasih atas kunjungan Anda di SLL</span>
                    </div>
                    <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-emerald-900 font-mono text-xs space-y-1">
                        <div class="flex justify-between">
                            <span>Status:</span>
                            <span class="font-bold text-emerald-700">LUNAS</span>
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
                    let channel = null;
                    try {
                        channel = new BroadcastChannel('sll_pos_customer_display');
                        channel.onmessage = (event) => {
                            this.handleMessage(event.data);
                        };
                    } catch (e) {
                        console.warn('BroadcastChannel tidak aktif:', e);
                    }

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
                            this.mode = 'IDLE';
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
