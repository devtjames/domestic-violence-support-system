<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-10 items-center justify-center whitespace-nowrap rounded-lg border border-transparent bg-teal-700 px-4 py-2 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:bg-teal-900 disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
