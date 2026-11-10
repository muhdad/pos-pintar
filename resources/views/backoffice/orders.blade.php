<x-app-layout>
    @section('page_title', 'Riwayat Transaksi')

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Riwayat Transaksi & Struk</h1>
                <p class="text-xs text-slate-500 mt-0.5">Daftar rekonsiliasi seluruh transaksi kasir online dan offline yang telah tersinkronisasi.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pos.index') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs tracking-wide shadow-md shadow-blue-500/20 transition flex items-center gap-1.5">
                    <span>+ Transaksi Baru</span>
                </a>
            </div>
        </div>

        <!-- Orders Table Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold border-b border-slate-100 uppercase tracking-wider">
                            <th class="pb-3">No. Struk</th>
                            <th class="pb-3">Waktu Transaksi</th>
                            <th class="pb-3">Kasir / Staf</th>
                            <th class="pb-3">Rincian Menu (Item)</th>
                            <th class="pb-3">Metode Bayar</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3 text-right">Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 font-bold text-slate-900 font-mono">
                                    {{ $order->order_number }}
                                </td>
                                <td class="py-3.5 text-slate-600">
                                    {{ $order->created_at->format('d/m/Y H:i') }} WIB
                                </td>
                                <td class="py-3.5 font-medium text-slate-800">
                                    {{ $order->user->name ?? 'Kasir Default' }}
                                </td>
                                <td class="py-3.5 text-slate-600">
                                    <div class="space-y-1">
                                        @foreach ($order->items as $it)
                                            <div class="text-[11px]">
                                                <strong>{{ $it->quantity }}x</strong> {{ $it->product->name ?? 'Menu' }}
                                                <span class="text-slate-400">(@Rp{{ number_format($it->unit_price, 0, ',', '.') }})</span>
                                                <span class="text-[9px] text-blue-500 bg-blue-50 px-1 py-0.2 rounded font-mono">HPP: Rp{{ number_format($it->unit_cost, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $order->payments->first()->method ?? 'CASH' }}
                                    </span>
                                </td>
                                <td class="py-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $order->status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $order->status === 'PAID' ? '✓ Lunas' : $order->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 text-right font-black text-slate-900">
                                    Rp{{ number_format($order->final_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Belum ada transaksi tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($orders->hasPages())
                <div class="mt-4 pt-3 border-t border-slate-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

