<x-app-layout>
    @section('page_title', 'Riwayat Transaksi')

    <div x-data="{
        detailModalOpen: false,
        selectedOrder: null,
        openDetail(order) {
            this.selectedOrder = order;
            this.detailModalOpen = true;
        }
    }" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-stone-200/60">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-stone-900 tracking-tight">Riwayat Transaksi & Struk</h1>
                <p class="text-xs text-stone-500 mt-0.5">Daftar rekonsiliasi seluruh transaksi kasir online dan offline yang telah tersinkronisasi.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pos.index') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-xs tracking-wide shadow-sm shadow-orange-500/20 transition flex items-center gap-1.5 active:scale-95">
                    <span>+ Transaksi Baru (POS)</span>
                </a>
            </div>
        </div>

        <!-- Orders Table Card -->
        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-stone-400 font-bold border-b border-stone-100 bg-stone-50/60 uppercase text-[10px] tracking-wider">
                            <th class="py-3.5 px-6">No. Struk</th>
                            <th class="py-3.5 px-4">Waktu Transaksi</th>
                            <th class="py-3.5 px-4">Kasir / Staf</th>
                            <th class="py-3.5 px-4">Rincian Menu (Item)</th>
                            <th class="py-3.5 px-4">Metode Bayar</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Total Transaksi</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="py-4 px-6 font-bold text-stone-900 font-mono">
                                    {{ $order->order_number }}
                                </td>
                                <td class="py-4 px-4 text-stone-600">
                                    {{ $order->created_at->format('d/m/Y H:i') }} WIB
                                </td>
                                <td class="py-4 px-4 font-medium text-stone-800">
                                    {{ $order->user->name ?? 'Kasir SLL' }}
                                </td>
                                <td class="py-4 px-4 text-stone-600">
                                    <div class="space-y-1">
                                        @foreach ($order->items as $it)
                                            <div class="text-[11px]">
                                                <strong>{{ $it->quantity }}x</strong> {{ $it->product->name ?? 'Menu' }}
                                                <span class="text-stone-400">(@Rp{{ number_format($it->unit_price, 0, ',', '.') }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                        {{ $order->payments->first()->method ?? 'CASH' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $order->status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $order->status === 'PAID' ? '✓ Lunas' : $order->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right font-black text-stone-900 font-mono text-sm">
                                    Rp{{ number_format($order->final_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button type="button" 
                                            @click="openDetail({{ json_encode($order) }})"
                                            class="px-2.5 py-1.5 rounded-lg bg-stone-50 hover:bg-amber-100 text-stone-600 hover:text-amber-900 border border-stone-200 text-xs font-bold transition flex items-center gap-1 ml-auto">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Detail</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-stone-400">
                                    <div class="w-12 h-12 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center mx-auto mb-2 text-xl">
                                        🧾
                                    </div>
                                    <p class="text-xs font-semibold text-stone-700">Belum ada transaksi tersimpan.</p>
                                    <p class="text-[11px] text-stone-400 mt-0.5">Buka Layar Kasir untuk mulai mencatat pesanan penjualan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($orders->hasPages())
                <div class="p-4 border-t border-stone-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        <!-- Detail Struk Order Modal -->
        <div x-show="detailModalOpen" 
             class="fixed inset-0 bg-stone-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-stone-200 animate-in fade-in zoom-in duration-150"
                 @click.outside="detailModalOpen = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <div>
                        <h3 class="text-sm font-black text-stone-900">Rincian Struk Transaksi</h3>
                        <span class="text-[10px] text-amber-700 font-bold font-mono" x-text="selectedOrder?.order_number"></span>
                    </div>
                    <button type="button" @click="detailModalOpen = false" class="text-stone-400 hover:text-stone-600 font-bold p-1">✕</button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div class="p-3 rounded-2xl bg-stone-50 border border-stone-200/80 space-y-1.5">
                        <div class="flex justify-between text-stone-500">
                            <span>Status:</span>
                            <span class="font-bold text-emerald-700">✓ LUNAS</span>
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
                    <div class="space-y-2 border-t border-b border-stone-100 py-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">Daftar Item Menu:</span>
                        <template x-for="item in (selectedOrder?.items || [])" :key="item.id">
                            <div class="flex justify-between items-start text-xs">
                                <div>
                                    <span class="font-bold text-stone-900" x-text="item.quantity + 'x ' + (item.product?.name || 'Menu')"></span>
                                    <span class="text-[10px] text-stone-400 block" x-text="'@Rp' + Number(item.unit_price).toLocaleString('id-ID')"></span>
                                </div>
                                <span class="font-bold text-stone-900 font-mono" x-text="'Rp' + Number(item.subtotal).toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Total Breakdown -->
                    <div class="space-y-1.5 pt-1">
                        <div class="flex justify-between text-stone-500">
                            <span>Subtotal:</span>
                            <span class="font-mono text-stone-800" x-text="'Rp' + Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-stone-500">
                            <span>Pajak (PPN 11%):</span>
                            <span class="font-mono text-stone-800" x-text="'Rp' + Number(selectedOrder?.tax_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm font-black text-stone-900 pt-1.5 border-t border-dashed border-stone-200">
                            <span>Total Bayar:</span>
                            <span class="text-amber-700 font-mono" x-text="'Rp' + Number(selectedOrder?.final_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex gap-2">
                    <button type="button" 
                            @click="detailModalOpen = false" 
                            class="flex-1 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs transition">
                        Tutup
                    </button>
                    <button type="button" 
                            @click="window.print()" 
                            class="flex-1 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5">
                        <span>🖨️ Cetak Struk</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>