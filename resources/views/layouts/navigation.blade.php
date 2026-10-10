@php
    $user = Auth::user();
    $unreadNotificationCount = $user->unreadNotifications()->count();
    $dashboardActive = request()->routeIs('dashboard') || request()->routeIs('officer.dashboard') || request()->routeIs('admin.dashboard');

    $navigationGroups = [
        'Workspace' => [
            ['label' => 'Dashboard', 'href' => route($user->dashboardRouteName()), 'active' => $dashboardActive, 'icon' => 'dashboard'],
        ],
    ];

    if ($user->isRegisteredUser()) {
        $navigationGroups['Workspace'][] = ['label' => 'Request support', 'href' => route('cases.create'), 'active' => request()->routeIs('cases.create'), 'icon' => 'plus'];
        $navigationGroups['Workspace'][] = ['label' => 'My cases', 'href' => route('cases.index'), 'active' => request()->routeIs('cases.index') || request()->routeIs('cases.show'), 'icon' => 'cases'];
        $navigationGroups['Information'] = [
            ['label' => 'Resources', 'href' => route('public.resources'), 'active' => request()->routeIs('public.resources'), 'icon' => 'resources'],
            ['label' => 'Awareness', 'href' => route('public.awareness'), 'active' => request()->routeIs('public.awareness*'), 'icon' => 'articles'],
        ];
    }

    if ($user->isSupportOfficer()) {
        $navigationGroups['Workspace'][] = ['label' => 'Case queue', 'href' => route('officer.cases.index'), 'active' => request()->routeIs('officer.cases.*'), 'icon' => 'cases'];
        $navigationGroups['Information'] = [
            ['label' => 'Resources', 'href' => route('public.resources'), 'active' => request()->routeIs('public.resources'), 'icon' => 'resources'],
        ];
    }

    if ($user->isAdmin()) {
        $navigationGroups['Workspace'][] = ['label' => 'Cases', 'href' => route('admin.cases.index'), 'active' => request()->routeIs('admin.cases.*'), 'icon' => 'cases'];
        $navigationGroups['People'] = [
            ['label' => 'Users', 'href' => route('admin.users.index'), 'active' => request()->routeIs('admin.users.*'), 'icon' => 'users'],
            ['label' => 'Support officers', 'href' => route('admin.support-officers.index'), 'active' => request()->routeIs('admin.support-officers.*'), 'icon' => 'officers'],
            ['label' => 'Contact messages', 'href' => route('admin.contact-messages.index'), 'active' => request()->routeIs('admin.contact-messages.*'), 'icon' => 'contact'],
        ];
        $navigationGroups['Content'] = [
            ['label' => 'Articles', 'href' => route('admin.awareness.articles.index'), 'active' => request()->routeIs('admin.awareness.articles.*'), 'icon' => 'articles'],
            ['label' => 'Categories', 'href' => route('admin.awareness.categories.index'), 'active' => request()->routeIs('admin.awareness.categories.*'), 'icon' => 'categories'],
            ['label' => 'Abuse types', 'href' => route('admin.abuse-types.index'), 'active' => request()->routeIs('admin.abuse-types.*'), 'icon' => 'alert'],
            ['label' => 'Resources', 'href' => route('admin.resources.index'), 'active' => request()->routeIs('admin.resources.*'), 'icon' => 'resources'],
            ['label' => 'FAQs', 'href' => route('admin.faqs.index'), 'active' => request()->routeIs('admin.faqs.*') || request()->routeIs('admin.faq-categories.*'), 'icon' => 'faq'],
        ];
        $navigationGroups['System'] = [
            ['label' => 'Reports', 'href' => route('admin.reports.index'), 'active' => request()->routeIs('admin.reports.*'), 'icon' => 'reports'],
            ['label' => 'Audit logs', 'href' => route('admin.audit-logs.index'), 'active' => request()->routeIs('admin.audit-logs.*'), 'icon' => 'audit'],
            ['label' => 'Settings', 'href' => route('admin.settings.edit'), 'active' => request()->routeIs('admin.settings.*'), 'icon' => 'settings'],
        ];
    }

    $roleLabel = str_replace('_', ' ', str($user->role)->headline());
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp

<nav
    x-data="{ sidebarOpen: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', sidebarOpen)"
    @keydown.escape.window="sidebarOpen = false"
    aria-label="Application navigation"
>
    <div class="fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur lg:hidden">
        <a href="{{ route($user->dashboardRouteName()) }}" class="flex min-w-0 items-center gap-3">
            <x-application-logo class="h-9 w-9 shrink-0" />
            <span class="truncate text-sm font-bold text-slate-900">{{ \App\Models\SystemSetting::platformName() }}</span>
        </a>

        <div class="flex items-center gap-1">
            <a href="{{ route('notifications.index') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-slate-900" aria-label="Notifications">
                <x-nav-icon name="bell" />
                @if ($unreadNotificationCount > 0)
                    <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white">{{ min($unreadNotificationCount, 99) }}</span>
                @endif
            </a>
            <button type="button" @click="sidebarOpen = true" class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-700 transition hover:bg-slate-100" aria-label="Open navigation" :aria-expanded="sidebarOpen.toString()">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" /></svg>
            </button>
        </div>
    </div>

    <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-800 bg-slate-950 text-slate-100 shadow-2xl transition-transform duration-300 ease-out lg:translate-x-0 lg:shadow-none"
        aria-label="Main menu"
    >
        <div class="flex h-20 shrink-0 items-center justify-between border-b border-white/10 px-5">
            <a href="{{ route($user->dashboardRouteName()) }}" class="flex min-w-0 items-center gap-3">
                <x-application-logo class="h-10 w-10 shrink-0" />
                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold text-white">{{ \App\Models\SystemSetting::platformName() }}</span>
                    <span class="mt-0.5 block text-xs font-medium text-slate-400">{{ $roleLabel }} portal</span>
                </span>
            </a>
            <button type="button" @click="sidebarOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-white/10 hover:text-white lg:hidden" aria-label="Close navigation">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-5">
            @foreach ($navigationGroups as $group => $items)
                <div class="mb-6 last:mb-0">
                    <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">{{ $group }}</p>
                    <div class="space-y-1">
                        @foreach ($items as $item)
                            <a href="{{ $item['href'] }}" class="group flex min-h-10 items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ $item['active'] ? 'bg-teal-500/15 text-teal-200 ring-1 ring-inset ring-teal-400/20' : 'text-slate-300 hover:bg-white/[0.07] hover:text-white' }}" @if($item['active']) aria-current="page" @endif>
                                <x-nav-icon :name="$item['icon']" class="h-5 w-5 shrink-0 {{ $item['active'] ? 'text-teal-300' : 'text-slate-500 transition group-hover:text-slate-300' }}" />
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="shrink-0 border-t border-white/10 p-3">
            <a href="{{ route('notifications.index') }}" class="mb-2 flex min-h-10 items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/[0.07] hover:text-white {{ request()->routeIs('notifications.*') ? 'bg-white/[0.07] text-white' : '' }}">
                <x-nav-icon name="bell" class="h-5 w-5 text-slate-500" />
                <span class="flex-1">Notifications</span>
                @if ($unreadNotificationCount > 0)
                    <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[11px] font-bold text-white">{{ min($unreadNotificationCount, 99) }}</span>
                @endif
            </a>

            <div x-data="{ userOpen: false }" class="relative">
                <button type="button" @click="userOpen = ! userOpen" class="flex w-full items-center gap-3 rounded-xl bg-white/[0.06] p-2.5 text-left transition hover:bg-white/10" :aria-expanded="userOpen.toString()">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-500/20 text-xs font-bold text-teal-200">{{ $initials }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-white">{{ $user->name }}</span>
                        <span class="block truncate text-xs text-slate-400">{{ $user->email }}</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-500 transition" :class="userOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                </button>

                <div x-show="userOpen" x-cloak x-transition.origin.bottom class="absolute bottom-full left-0 right-0 mb-2 overflow-hidden rounded-xl border border-slate-700 bg-slate-900 p-1.5 shadow-xl">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-200 transition hover:bg-white/10 hover:text-white">
                        <x-nav-icon name="profile" class="h-4 w-4" /> Profile settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm text-slate-200 transition hover:bg-white/10 hover:text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 17l5-5-5-5m5 5H3m10-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" /></svg>
                            Log out
                        </button>
                    </form>
                </div>
            </div>

            <x-quick-exit-button class="mt-3 w-full bg-rose-600 hover:bg-rose-500 focus:ring-offset-slate-950" />
            <p class="mt-2 px-2 text-[11px] leading-4 text-slate-500">Quick Exit does not erase browser history.</p>
        </div>
    </aside>
</nav>
