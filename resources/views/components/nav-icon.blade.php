@props(['name'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    @switch($name)
        @case('dashboard') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z" /> @break
        @case('plus') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14m-7-7h14" /> @break
        @case('cases') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 6V4h6v2m-9 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm-2 5h16M9 14h6" /> @break
        @case('users') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-1a3 3 0 0 0 0-6m3 17v-1.5a4.5 4.5 0 0 0-2.7-4.1" /> @break
        @case('officers') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3 5 6v5c0 4.6 2.9 8.4 7 10 4.1-1.6 7-5.4 7-10V6l-7-3Zm-3 8 2 2 4-4" /> @break
        @case('articles') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 3h9l3 3v15H6V3Zm8 0v4h4M9 11h6m-6 4h6m-6 4h4" /> @break
        @case('categories') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z" /> @break
        @case('alert') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.3 3.8 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.8a2 2 0 0 0-3.4 0Z" /> @break
        @case('resources') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21V5.5ZM20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5A2.5 2.5 0 0 1 20 21V5.5Z" /> @break
        @case('faq') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm-2.2-11a2.3 2.3 0 1 1 3.5 2c-.8.5-1.3 1-1.3 2m0 3h.01" /> @break
        @case('contact') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5h18v14H3V5Zm1 1 8 7 8-7" /> @break
        @case('reports') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 20V10m5 10V4m6 16v-7m5 7V7" /> @break
        @case('audit') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 4H5v16h14V4h-4M9 3h6v4H9V3Zm0 9h6m-6 4h4" /> @break
        @case('settings') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm7.4-2.1.1-1.4-.1-1.4 2-1.5-2-3.4-2.4 1a8 8 0 0 0-2.4-1.4L14.3 3h-4.6l-.4 2.3A8 8 0 0 0 7 6.7l-2.4-1-2 3.4 2 1.5-.1 1.4.1 1.4-2 1.5 2 3.4 2.4-1a8 8 0 0 0 2.4 1.4l.4 2.3h4.6l.4-2.3a8 8 0 0 0 2.4-1.4l2.4 1 2-3.4-2.2-1.5Z" /> @break
        @case('bell') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12h4" /> @break
        @case('profile') <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 9a7 7 0 0 0-14 0" /> @break
        @default <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14M12 5l7 7-7 7" />
    @endswitch
</svg>
