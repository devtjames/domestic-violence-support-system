<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-10 items-center justify-center whitespace-nowrap rounded-lg border border-transparent bg-rose-700 px-4 py-2 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-rose-800 active:bg-rose-900 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
