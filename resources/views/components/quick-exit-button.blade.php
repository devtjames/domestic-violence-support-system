@props([
    'label' => 'Quick Exit',
    'class' => '',
])

<button
    type="button"
    data-quick-exit
    onclick="window.location.replace('{{ route('public.quick-exit') }}')"
    aria-label="{{ $label }}"
    {{ $attributes->merge(['class' => trim('inline-flex min-h-10 items-center justify-center whitespace-nowrap rounded-lg bg-rose-700 px-4 py-2 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 '.$class)]) }}
>
    {{ $label }}
</button>
