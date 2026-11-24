@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white border border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/30 rounded-xl text-xs text-slate-800 shadow-xs px-3.5 py-2.5 transition']) }}>
