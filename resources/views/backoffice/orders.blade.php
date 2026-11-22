<x-app-layout>
    @section('page_title', 'Riwayat Transaksi')

    <div x-data="{
        detailModalOpen: false,
        selectedOrder: null,
        openDetail(order) {
            this.selectedOrder = order;
            this.detailModalOpen = true;
        }
    }" class="space-y-5">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[#E7E0D8]">
            <div>
                <h1 class="text-lg sm:text-xl font-display font-bold text-[#5B0A14] tracking-tight">Riwayat Transaksi & Struk</h1>
                <p class="text-xs text-stone-500 mt-0.5">Daftar rekonsiliasi seluruh transaksi kasir online dan offline yang telah tersinkronisasi.</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Export CSV / Excel Button -->
                <a href="{{ route('backoffice.orders.export-csv') }}" 
                   class="px-3 py-1.5 rounded-[6px] bg-[#5B0A14] hover:bg-[#7A0F1C] text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-subtle active:translate-y-px">
                    <svg class="w-3.5 h-3.5 text-[#FFB800]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export CSV / Excel</span>
                </a>

                <a href="{{ route('pos.index') }}" class="px-3.5 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-bold text-xs tracking-wide transition shadow-subtle flex items-center gap-1.5 active:translate-y-px">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Transaksi Baru (POS)</span>
                </a>
            </div>
        </div>

        <!-- Orders Table Card -->
        <div class="bg-white rounded-[10px] border border-[#E7E0D8] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-xs">
                    <thead>
                        <tr class="text-stone-500 font-bold border-b border-[#E7E0D8] bg-stone-50 uppercase text-[10px] tracking-wider sticky top-0 z-10">
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
                            <tr class="hover:bg-stone-50/70 transition">
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
                                        {{ $order->status === 'PAID' ? 'Lunas' : $order->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono tabular-nums font-bold text-stone-900">
                                    Rp{{ number_format($order->final_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button type="button" 
                                            @click="openDetail({{ json_encode($order) }})"
                                            class="px-2 py-1 rounded-[4px] bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 text-xs font-semibold transition ml-auto">
                                        Detail
                                    </button>
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

        <!-- Detail Struk Modal -->
        <div x-show="detailModalOpen" 
             class="fixed inset-0 bg-stone-900/50 z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-[10px] max-w-sm w-full p-5 border border-[#E7E0D8] shadow-modal animate-in fade-in duration-150"
                 @click.outside="detailModalOpen = false">
                
                <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                    <div>
                        <h3 class="text-xs font-display font-bold text-stone-900 uppercase tracking-wide">Rincian Struk Transaksi</h3>
                        <span class="text-[10px] text-stone-500 font-mono font-bold" x-text="selectedOrder?.order_number"></span>
                    </div>
                    <button type="button" @click="detailModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold p-1">✕</button>
                </div>

                <div class="mt-3 space-y-2.5 text-xs">
                    <div class="p-2.5 rounded-[6px] bg-stone-50 border border-stone-200 space-y-1">
                        <div class="flex justify-between text-stone-500">
                            <span>Status:</span>
                            <span class="font-bold text-emerald-700">Lunas</span>
                        </div>
                        <div class="flex justify-between text-stone-500">
                            <span>Kasir:</span>
                            <span class="font-medium text-stone-800" x-text="selectedOrder?.user?.name || 'Kasir SLL'"></span>
                        </div>
                        <div class="flex justify-between text-stone-500">
                            <span>Pelanggan:</span>
                            <span class="font-medium text-stone-800" x-text="selectedOrder?.customer_name || 'Umum / Dine-in'"></span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-1.5 border-t border-b border-stone-100 py-2.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">Daftar Item:</span>
                        <template x-for="item in (selectedOrder?.items || [])" :key="item.id">
                            <div class="flex justify-between items-start text-xs">
                                <div>
                                    <span class="font-bold text-stone-900" x-text="item.quantity + 'x ' + (item.product?.name || 'Menu')"></span>
                                    <span class="text-[10px] text-stone-400 font-mono tabular-nums block" x-text="'@Rp' + Number(item.unit_price).toLocaleString('id-ID')"></span>
                                </div>
                                <span class="font-bold text-stone-900 font-mono tabular-nums" x-text="'Rp' + Number(item.subtotal).toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Total Breakdown -->
                    <div class="space-y-1 pt-0.5">
                        <div class="flex justify-between text-stone-500">
                            <span>Subtotal:</span>
                            <span class="font-mono tabular-nums text-stone-800" x-text="'Rp' + Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-stone-500">
                            <span>Pajak (PPN 11%):</span>
                            <span class="font-mono tabular-nums text-stone-800" x-text="'Rp' + Number(selectedOrder?.tax_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-stone-900 pt-1.5 border-t border-stone-200">
                            <span>Total Bayar:</span>
                            <span class="text-[#5B0A14] font-mono tabular-nums font-bold" x-text="'Rp' + Number(selectedOrder?.final_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>

                <template x-if="selectedOrder && selectedOrder.status !== 'VOIDED'">
                    <form :action="'/backoffice/orders/' + selectedOrder.id + '/void'" 
                          method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini (VOID)? Stok bahan baku akan otomatis dikembalikan ke gudang.')" 
                          class="mt-2.5 pt-2 border-t border-stone-100 flex items-center gap-1.5">
                        @csrf
                        <input type="text" name="reason" required placeholder="Alasan void (misal: salah input kasir)" class="flex-1 text-[11px] bg-stone-50 border border-stone-200 rounded-[4px] px-2 py-1 text-stone-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500">
                        <button type="submit" class="px-2.5 py-1 rounded-[4px] bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shrink-0">
                            Void Pesanan
                        </button>
                    </form>
                </template>

                <div class="mt-4 flex gap-2">
                    <button type="button" 
                            @click="detailModalOpen = false" 
                            class="flex-1 py-1.5 rounded-[6px] bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium text-xs transition">
                        Tutup
                    </button>
                    <button type="button" 
                            @click="window.print()" 
                            class="flex-1 py-1.5 rounded-[6px] bg-[#EA580C] hover:bg-[#D94E07] text-white font-bold text-xs transition flex items-center justify-center gap-1.5">
                        <span>Cetak Struk (58mm)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden Printable Thermal Receipt Container for Backoffice (58mm / 80mm standard) -->
        <div id="printable-receipt" class="hidden text-black font-mono text-[11px] leading-tight select-none">
            <div class="text-center pb-2 border-b border-dashed border-black">
                <h2 class="text-xs font-bold tracking-wider uppercase">SOTO LAMONGAN LESTARI</h2>
                <p class="text-[10px] mt-0.5">{{ $outlet->name ?? 'Cabang Utama' }}</p>
                <p class="text-[10px]">Telp: 0852-1325-4219</p>
            </div>

            <div class="py-1.5 border-b border-dashed border-black text-[10px] space-y-0.5">
                <div class="flex justify-between">
                    <span>No: <strong x-text="selectedOrder?.order_number"></strong></span>
                    <span x-text="selectedOrder?.created_at ? new Date(selectedOrder.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) : ''"></span>
                </div>
                <div class="flex justify-between">
                    <span>Tgl: <span x-text="selectedOrder?.created_at ? new Date(selectedOrder.created_at).toLocaleDateString('id-ID') : ''"></span></span>
                    <span class="font-bold uppercase" x-text="selectedOrder?.status"></span>
                </div>
                <div class="flex justify-between" x-show="selectedOrder?.customer_name">
                    <span>Plg: <span x-text="selectedOrder?.customer_name"></span></span>
                </div>
                <div class="flex justify-between" x-show="selectedOrder?.user?.name">
                    <span>Kasir: <span x-text="selectedOrder?.user?.name"></span></span>
                </div>
            </div>

            <!-- Items -->
            <div class="py-1.5 border-b border-dashed border-black space-y-1">
                <template x-for="item in (selectedOrder?.items || [])" :key="item.id">
                    <div>
                        <div class="font-bold text-[11px]" x-text="item.product?.name || item.name"></div>
                        <div class="flex justify-between text-[10px]">
                            <span x-text="`${item.quantity} x ${Number(item.unit_price).toLocaleString('id-ID')}`"></span>
                            <span class="font-bold" x-text="Number(item.subtotal).toLocaleString('id-ID')"></span>
                        </div>
                        <template x-if="item.notes">
                            <div class="text-[9px] italic" x-text="`* ${item.notes}`"></div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Totals -->
            <div class="py-1.5 border-b border-dashed border-black text-[10px] space-y-0.5">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span x-text="Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between" x-show="selectedOrder?.discount_amount > 0">
                    <span>Diskon</span>
                    <span x-text="`-${Number(selectedOrder?.discount_amount || 0).toLocaleString('id-ID')}`"></span>
                </div>
                <div class="flex justify-between">
                    <span>PPN (11%)</span>
                    <span x-text="Number(selectedOrder?.tax_amount || 0).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between font-bold text-xs pt-1 border-t border-dotted border-black">
                    <span>TOTAL</span>
                    <span x-text="`Rp${Number(selectedOrder?.final_amount || 0).toLocaleString('id-ID')}`"></span>
                </div>
            </div>

            <!-- Payment info -->
            <div class="py-1.5 border-b border-dashed border-black text-[10px] space-y-0.5">
                <div class="flex justify-between">
                    <span>Metode</span>
                    <span class="font-bold uppercase" x-text="(selectedOrder?.payments && selectedOrder.payments[0]) ? selectedOrder.payments[0].method : 'CASH'"></span>
                </div>
                <div class="flex justify-between">
                    <span>Status</span>
                    <span class="font-bold text-black">LUNAS</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center pt-2 text-[10px] space-y-0.5">
                <p class="font-bold">Rasa Mantap, Harga Bersahabat</p>
                <p>Terima Kasih Atas Kunjungan Anda!</p>
                <p class="text-[8px] text-gray-500 pt-1">SLL Smart POS Cloud</p>
            </div>
        </div>
    </div>
</x-app-layout>
