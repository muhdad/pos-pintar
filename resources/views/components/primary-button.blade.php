<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-xs active:translate-y-px focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-1']) }}>
    {{ $slot }}
</button>
