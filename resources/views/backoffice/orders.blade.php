<x-app-layout>
    @section('page_title', 'Riwayat Transaksi')

    <div x-data="{
        ordersList: {{ Js::from($orders->items()) }},
        detailModalOpen: false,
        selectedOrder: null,

        openDetail(orderId) {
            this.selectedOrder = this.ordersList.find(o => String(o.id) === String(orderId) || String(o.order_number) === String(orderId)) || null;
            this.detailModalOpen = true;
        },

        printCurrentReceipt() {
            if (!this.selectedOrder) return;
            if (window.thermalPrinter) {
                window.thermalPrinter.printReceipt(this.selectedOrder);
                return;
            }
            setTimeout(() => {
                window.print();
            }, 50);
        }
    }">
        <div id="backoffice-orders-ui" class="space-y-5">
            <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-stone-200/80">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-stone-800 tracking-tight">Riwayat Transaksi & Struk</h1>
                <p class="text-xs text-stone-500 mt-0.5">Daftar rekonsiliasi seluruh transaksi kasir online dan offline yang telah tersinkronisasi.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Connect Printer Thermal Button for Tablet -->
                <button type="button" 
                        onclick="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                        class="px-3.5 py-2 rounded-xl bg-white border border-stone-200 text-stone-700 hover:bg-stone-50 font-semibold text-xs shadow-xs transition flex items-center gap-1.5"
                        title="Hubungkan Printer Thermal 58mm via Bluetooth">
                    <span>🖨️</span>
                    <span x-text="window.thermalPrinter?.isConnected ? (window.thermalPrinter.printerName || 'Printer 58mm') : 'Printer 58mm'"></span>
                </button>

                <!-- Export Excel Button -->
                <a href="{{ route('backoffice.orders.export-excel', request()->query()) }}" 
                   class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Excel (.xls)</span>
                </a>

                <!-- Export CSV Button -->
                <a href="{{ route('backoffice.orders.export-csv', request()->query()) }}" 
                   class="px-3.5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium text-xs transition">
                    <span>CSV</span>
                </a>

                <a href="{{ route('pos.index') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5 active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Kasir POS (F2)</span>
                </a>
            </div>
        </div>

        <!-- Orders View for Tablet & Mobile (Card Layout - Touch Friendly) -->
        <div class="block md:hidden space-y-3">
            @forelse ($orders as $order)
                <div class="bg-white rounded-2xl border border-stone-200/80 p-4 shadow-xs space-y-2.5 active:bg-stone-50 transition"
                     @click="openDetail('{{ $order->id }}')">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs">
                                🧾
                            </span>
                            <div>
                                <span class="font-bold text-xs text-stone-900 font-mono block">{{ $order->order_number }}</span>
                                <span class="text-[10px] text-stone-500 font-mono">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->status === 'PAID' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                            {{ $order->status === 'PAID' ? 'Lunas' : ($order->status === 'VOIDED' ? 'Batal' : $order->status) }}
                        </span>
                    </div>

                    <!-- Items Preview -->
                    <div class="text-[11px] text-stone-600 bg-stone-50 p-2.5 rounded-xl border border-stone-100 space-y-0.5">
                        @foreach ($order->items as $it)
                            <div class="flex justify-between items-center">
                                <span class="truncate"><strong class="text-stone-800">{{ $it->quantity }}x</strong> {{ $it->product->name ?? 'Menu' }}</span>
                                <span class="text-stone-500 font-mono tabular-nums shrink-0 ml-2">Rp{{ number_format($it->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div>
                            <span class="text-[10px] text-stone-400 block">Total Tagihan</span>
                            <span class="text-sm font-bold text-emerald-700 font-mono tabular-nums">
                                Rp{{ number_format($order->final_amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-stone-100 text-stone-700 border border-stone-200">
                                {{ $order->payments->first()->method ?? 'CASH' }}
                            </span>
                            <button type="button" 
                                    @click.stop="openDetail('{{ $order->id }}')"
                                    class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-xs active:scale-95 transition">
                                Buka Struk
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-stone-200/80 p-8 text-center text-xs text-stone-400">
                    Belum ada riwayat transaksi tersimpan.
                </div>
            @endforelse
        </div>

        <!-- Orders Table Card for Desktop & Tablet Landscape -->
        <div class="hidden md:block bg-white rounded-2xl border border-stone-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-xs">
                    <thead>
                        <tr class="text-stone-500 font-semibold border-b border-stone-200/80 bg-stone-50/70 text-[11px] sticky top-0 z-10">
                            <th class="py-2.5 px-4">No. Struk</th>
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Kasir</th>
                            <th class="py-2.5 px-3">Item Pesanan</th>
                            <th class="py-2.5 px-3">Metode</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Total Tagihan</th>
                            <th class="py-2.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-emerald-50/40 transition cursor-pointer"
                                @click="openDetail('{{ $order->id }}')">
                                <td class="py-3 px-4 font-bold text-stone-900 font-mono tabular-nums">
                                    {{ $order->order_number }}
                                </td>
                                <td class="py-3 px-3 text-stone-500 font-mono tabular-nums text-[11px]">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-3 font-medium text-stone-800">
                                    {{ $order->user->name ?? 'Kasir SLL' }}
                                </td>
                                <td class="py-3 px-3 text-stone-700 max-w-xs">
                                    <div class="space-y-0.5">
                                        @foreach ($order->items as $it)
                                            <div class="text-[11px] truncate">
                                                <span class="font-bold text-stone-900">{{ $it->quantity }}x</span> {{ $it->product->name ?? 'Menu' }}
                                                <span class="text-stone-400 font-mono tabular-nums">(@Rp{{ number_format($it->unit_price, 0, ',', '.') }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-block px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold bg-stone-100 text-stone-700 border border-stone-200">
                                        {{ $order->payments->first()->method ?? 'CASH' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-block px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold {{ $order->status === 'PAID' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                                        {{ $order->status === 'PAID' ? 'Lunas' : ($order->status === 'VOIDED' ? 'Batal (VOID)' : $order->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums font-bold text-stone-900">
                                    Rp{{ number_format($order->final_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right" @click.stop>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                @click="openDetail('{{ $order->id }}')"
                                                class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-semibold transition">
                                            Buka Struk
                                        </button>
                                        <form action="{{ route('backoffice.orders.destroy', $order) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Hapus permanen transaksi #{{ $order->order_number }}? Stok bahan baku otomatis dikembalikan dan tidak akan masuk ke hitungan omzet/cashflow.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    title="Hapus Transaksi Testing"
                                                    class="p-1 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-xs text-stone-400">
                                    Belum ada transaksi tersimpan. Buka Layar Kasir untuk mulai bertransaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($orders->hasPages())
                <div class="p-3 border-t border-stone-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        <!-- Detail Struk Modal (Ringan, Tanpa Backdrop-Blur Berat, Touch Friendly) -->
        <div x-show="detailModalOpen" 
             @click.self="detailModalOpen = false" 
             class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-sm sm:max-w-md w-full p-4 sm:p-6 border border-stone-200 shadow-xl max-h-[90vh] overflow-y-auto"
                 @click.stop>
                
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <div>
                        <h3 class="text-xs font-bold text-stone-800 uppercase tracking-wide">Rincian Struk Transaksi</h3>
                        <span class="text-xs text-emerald-700 font-mono font-bold" x-text="selectedOrder?.order_number"></span>
                    </div>
                    <button type="button" @click="detailModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1 text-sm">✕</button>
                </div>

                <div class="mt-3.5 space-y-2.5 text-xs">
                    <div class="p-3 rounded-xl bg-stone-50 border border-stone-200/80 space-y-1">
                        <div class="flex justify-between text-stone-500">
                            <span>Status:</span>
                            <span class="font-bold text-emerald-700" x-text="selectedOrder?.status === 'PAID' ? 'LUNAS' : selectedOrder?.status"></span>
                        </div>
                        <div class="flex justify-between text-stone-500">
                            <span>Kasir:</span>
                            <span class="font-medium text-stone-800" x-text="selectedOrder?.user?.name || 'Kasir SLL'"></span>
                        </div>
                        <div class="flex justify-between text-stone-500">
                            <span>Pelanggan:</span>
                            <span class="font-medium text-stone-800" x-text="selectedOrder?.customer_name || 'Umum / Dine-in'"></span>
                        </div>
                        <div class="flex justify-between text-stone-500">
                            <span>Waktu Transaksi:</span>
                            <span class="font-mono text-stone-700" x-text="selectedOrder?.created_at ? new Date(selectedOrder.created_at).toLocaleString('id-ID') : '-'"></span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-1.5 border-t border-b border-stone-100 py-2.5">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-stone-400 block">Daftar Item:</span>
                        <template x-for="item in (selectedOrder?.items || [])" :key="item.id">
                            <div class="flex justify-between items-start text-xs py-0.5">
                                <div>
                                    <span class="font-bold text-stone-800" x-text="item.quantity + 'x ' + (item.product?.name || 'Menu')"></span>
                                    <span class="text-[10px] text-stone-400 font-mono tabular-nums block" x-text="'@Rp' + Number(item.unit_price).toLocaleString('id-ID')"></span>
                                </div>
                                <span class="font-bold text-stone-800 font-mono tabular-nums" x-text="'Rp' + Number(item.subtotal).toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Total Breakdown -->
                    <div class="space-y-1 pt-0.5">
                        <div class="flex justify-between text-stone-500">
                            <span>Subtotal:</span>
                            <span class="font-mono tabular-nums text-stone-800" x-text="'Rp' + Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-stone-500" x-show="Number(selectedOrder?.discount_amount || 0) > 0">
                            <span>Diskon:</span>
                            <span class="font-mono tabular-nums text-rose-600" x-text="'-Rp' + Number(selectedOrder?.discount_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-stone-500" x-show="Number(selectedOrder?.tax_amount || 0) > 0">
                            <span>Pajak:</span>
                            <span class="font-mono tabular-nums text-stone-800" x-text="'Rp' + Number(selectedOrder?.tax_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-stone-900 pt-1.5 border-t border-stone-200">
                            <span>Total Bayar:</span>
                            <span class="text-emerald-700 font-mono tabular-nums font-bold text-sm" x-text="'Rp' + Number(selectedOrder?.final_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>

                <!-- Void Order Form jika diperlukan -->
                <template x-if="selectedOrder && selectedOrder.status !== 'VOIDED'">
                    <form :action="'/backoffice/orders/' + selectedOrder.id + '/void'" 
                          method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini (VOID)? Stok bahan baku akan otomatis dikembalikan ke gudang.')" 
                          class="mt-2.5 pt-2 border-t border-stone-100 flex items-center gap-1.5">
                        @csrf
                        <input type="text" name="reason" required placeholder="Alasan void (misal: salah input kasir)" class="flex-1 text-[11px] bg-stone-50 border border-stone-200 rounded-lg px-2.5 py-1.5 text-stone-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition shrink-0">
                            Void
                        </button>
                    </form>
                </template>

                <!-- Strip Status Printer Thermal -->
                <div class="p-2.5 bg-stone-50 rounded-xl border border-stone-200/80 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span class="text-[11px] text-stone-600 font-medium">Printer: <strong class="text-emerald-700 font-mono" x-text="window.thermalPrinter?.useRawBt ? 'RawBT (Android)' : (window.thermalPrinter?.isConnected ? (window.thermalPrinter.printerName || '58mm Terhubung') : 'Siap Cetak 58mm')"></strong></span>
                    <div class="flex items-center gap-1.5">
                        <button type="button" 
                                @click="window.thermalPrinter && window.thermalPrinter.toggleRawBt(); $forceUpdate()" 
                                class="text-[10px] font-bold px-2 py-1 rounded-lg border transition"
                                :class="window.thermalPrinter?.useRawBt ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-white text-stone-700 border-stone-200 hover:bg-stone-50'"
                                title="Gunakan aplikasi RawBT Android untuk printer Bluetooth">
                            <span x-text="window.thermalPrinter?.useRawBt ? '✓ RawBT Aktif' : 'Mode RawBT'"></span>
                        </button>
                        <button type="button" 
                                @click="window.thermalPrinter && window.thermalPrinter.connectBluetooth()" 
                                class="text-[10px] font-bold text-emerald-800 bg-white px-2 py-1 rounded-lg border border-stone-200 hover:bg-emerald-50">
                            Pairing BLE
                        </button>
                    </div>
                </div>

                <!-- Action Buttons: Print Struk & Tutup -->
                <div class="mt-4 flex gap-2">
                    <button type="button" 
                            @click="detailModalOpen = false" 
                            class="flex-1 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold text-xs transition">
                        Tutup
                    </button>
                    <button type="button" 
                            @click="printCurrentReceipt()" 
                            class="flex-1 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-xs active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Struk (58mm)</span>
                    </button>
                </div>
            </div>
        </div>
    </div> <!-- /#backoffice-orders-ui -->

    <!-- Hidden Printable Thermal Receipt Container for Backoffice (58mm / 80mm standard) -->
    <div id="printable-receipt" class="print-only text-black font-mono select-none" style="width: 48mm; max-width: 48mm; margin: 0 auto; page-break-inside: avoid; break-inside: avoid;">
        <div class="text-center pb-2 border-b border-dashed border-black">
            <h2 class="text-xs font-bold tracking-wider uppercase text-black">SOTO LAMONGAN LESTARI</h2>
            <p class="text-[10px] mt-0.5 font-semibold text-black">Cabang Utama</p>
            <p class="text-[10px] font-semibold text-black">Telp: 0852-1325-4219</p>
        </div>

        <div class="py-1.5 border-b border-dashed border-black text-[10px] space-y-0.5">
            <div class="flex justify-between">
                <span>No: <strong class="font-bold text-black" x-text="selectedOrder?.order_number"></strong></span>
                <span class="font-semibold text-black" x-text="selectedOrder?.created_at ? new Date(selectedOrder.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) : ''"></span>
            </div>
            <div class="flex justify-between">
                <span>Tgl: <span class="font-semibold text-black" x-text="selectedOrder?.created_at ? new Date(selectedOrder.created_at).toLocaleDateString('id-ID') : ''"></span></span>
                <span class="font-bold uppercase text-black" x-text="selectedOrder?.status"></span>
            </div>
            <div class="flex justify-between" x-show="selectedOrder?.customer_name">
                <span>Pelanggan:</span>
                <span class="font-semibold text-black" x-text="selectedOrder?.customer_name"></span>
            </div>
        </div>

        <!-- Items -->
        <div class="py-1.5 border-b border-dashed border-black space-y-1">
            <template x-for="item in (selectedOrder?.items || [])" :key="item.id">
                <div>
                    <div class="font-bold text-[11px] text-black" x-text="item.product?.name || 'Menu'"></div>
                    <div class="flex justify-between text-[10px]">
                        <span class="font-semibold text-black">
                            <span x-text="item.quantity"></span> x <span x-text="Number(item.unit_price).toLocaleString('id-ID')"></span>
                        </span>
                        <span class="font-bold text-black" x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Total -->
        <div class="py-1.5 border-b border-dashed border-black space-y-0.5 text-[10px]">
            <div class="flex justify-between">
                <span class="font-semibold text-black">Subtotal:</span>
                <span class="font-bold text-black" x-text="Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></span>
            </div>
            <div class="flex justify-between font-bold text-xs pt-1 border-t border-dotted border-black">
                <span class="text-black">TOTAL:</span>
                <span class="text-black">Rp<span x-text="Number(selectedOrder?.final_amount || 0).toLocaleString('id-ID')"></span></span>
            </div>
        </div>

        <div class="text-center pt-2 text-[10px] space-y-0.5 font-semibold text-black">
            <p class="font-bold">Terima Kasih Atas Kunjungan Anda!</p>
            <p>Rasa Mantap, Harga Bersahabat</p>
            <p class="text-[9px] pt-1">SLL Smart POS Cloud</p>
        </div>
    </div>
    </div>
</x-app-layout>
