<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 rounded-xl transition shadow-xs focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1']) }}>
    {{ $slot }}
</button>
