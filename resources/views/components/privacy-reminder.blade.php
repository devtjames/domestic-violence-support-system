@props(['class' => ''])

<div {{ $attributes->merge(['class' => trim('flex items-start gap-3 rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-3.5 text-sm leading-6 text-amber-950 '.$class)]) }}>
    <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.3 3.8 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.8a2 2 0 0 0-3.4 0Z" /></svg>
    <span>{{ $slot->isEmpty() ? 'Quick Exit leaves this website quickly. Quick Exit does not erase browser history, network logs, saved passwords, downloads, or device activity.' : $slot }}</span>
</div>
