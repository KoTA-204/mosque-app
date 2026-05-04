@php
    use App\Helpers\MenuHelper;
    $menuGroups = MenuHelper::getMenuGroups();
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="fixed flex flex-col top-0 left-0 bg-white dark:bg-gray-900 dark:border-gray-800
           text-gray-900 h-screen transition-all duration-300 ease-in-out z-[40] border-r border-gray-200 xl:translate-x-0"
    x-data="{
        openSubmenus: {},
        init() {
            this.initializeActiveMenus();
        },
        initializeActiveMenus() {
            const currentPath = '{{ $currentPath }}';

            @foreach ($menuGroups as $groupIndex => $menuGroup)
                @foreach ($menuGroup['items'] as $itemIndex => $item)
                    @if (isset($item['subItems']))
                        @foreach ($item['subItems'] as $subItem)
                            if (currentPath === '{{ ltrim($subItem['path'], '/') }}' ||
                                window.location.pathname === '{{ $subItem['path'] }}') {
                                this.openSubmenus['{{ $groupIndex }}-{{ $itemIndex }}'] = true;
                            }
                        @endforeach
                    @endif
                @endforeach
            @endforeach
        },
        toggleSubmenu(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            const newState = !this.openSubmenus[key];
            if (newState) {
                this.openSubmenus = {};
            }
            this.openSubmenus[key] = newState;
        },
        isSubmenuOpen(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            return this.openSubmenus[key] || false;
        },
        isActive(path) {
            return window.location.pathname === path || '{{ $currentPath }}' === path.replace(/^\//, '');
        }
    }"
    :class="{
        'w-[280px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
        'w-[72px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full': !$store.sidebar.isMobileOpen
    }"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">

    <!-- Logo + Toggle Section -->
    <div class="flex items-center justify-between px-4 border-b border-gray-100 dark:border-gray-800 h-[72px]">

        <!-- Logo & Name (expanded) -->
        <a href="/" class="flex items-center gap-3 overflow-hidden"
            x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
            <img class="dark:hidden flex-shrink-0" src="{{ asset('images/logo.png') }}" alt="Logo" width="40" height="40" />
            <img class="hidden dark:block flex-shrink-0" src="{{ asset('images/logo.png') }}" alt="Logo" width="40" height="40" />
            <span class="font-bold text-gray-900 dark:text-white leading-tight text-sm whitespace-nowrap">
                Masjid<br>Luqmanul Hakim
            </span>
        </a>

        <!-- Logo only (collapsed) -->
        <a href="/" class="flex items-center justify-center w-full"
            x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" width="36" height="36" />
        </a>
    </div>

    <!-- Navigation Menu -->
    <div class="flex flex-col flex-1 overflow-y-auto overflow-x-hidden py-4 px-3 no-scrollbar">
        <nav class="flex-1">
            @foreach ($menuGroups as $groupIndex => $menuGroup)
                <ul class="flex flex-col gap-1 mb-6">
                    @foreach ($menuGroup['items'] as $itemIndex => $item)
                        <li>
                            @if (isset($item['subItems']))
                                <button
                                    @click="toggleSubmenu({{ $groupIndex }}, {{ $itemIndex }})"
                                    class="flex items-center w-full rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-150 group"
                                    :class="[
                                        isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }})
                                            ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white',
                                        (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen)
                                            ? 'justify-center' : 'justify-start'
                                    ]">
                                    <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center"
                                        :class="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }})
                                            ? 'text-gray-900 dark:text-white'
                                            : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white'">
                                        {!! MenuHelper::getIconSvg($item['icon']) !!}
                                    </span>
                                    <span
                                        x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                        class="ml-3 flex-1 text-left whitespace-nowrap">
                                        {{ $item['name'] }}
                                        @if (!empty($item['new']))
                                            <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-brand-500 text-white">new</span>
                                        @endif
                                    </span>
                                    <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                        class="w-4 h-4 flex-shrink-0 transition-transform duration-200"
                                        :class="{ 'rotate-180 text-brand-500': isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) }"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) && ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen)"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0">
                                    <ul class="mt-1 ml-8 space-y-0.5">
                                        @foreach ($item['subItems'] as $subItem)
                                            <li>
                                                <a href="{{ $subItem['path'] }}"
                                                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors duration-150"
                                                    :class="isActive('{{ $subItem['path'] }}')
                                                        ? 'text-brand-600 dark:text-brand-400 font-medium bg-brand-50 dark:bg-brand-900/20'
                                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800'">
                                                    {{ $subItem['name'] }}
                                                    <span class="flex items-center gap-1 ml-auto">
                                                        @if (!empty($subItem['new']))
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-brand-500 text-white">new</span>
                                                        @endif
                                                        @if (!empty($subItem['pro']))
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-500 text-white">pro</span>
                                                        @endif
                                                    </span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a href="{{ $item['path'] }}"
                                    class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-150 group"
                                    :class="[
                                        isActive('{{ $item['path'] }}')
                                            ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white',
                                        (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen)
                                            ? 'justify-center' : 'justify-start'
                                    ]">
                                    <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center"
                                        :class="isActive('{{ $item['path'] }}')
                                            ? 'text-gray-900 dark:text-white'
                                            : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white'">
                                        {!! MenuHelper::getIconSvg($item['icon']) !!}
                                    </span>
                                    <span
                                        x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                        class="ml-3 whitespace-nowrap flex items-center gap-2">
                                        {{ $item['name'] }}
                                        @if (!empty($item['new']))
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-brand-500 text-white">new</span>
                                        @endif
                                    </span>
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </nav>

        <!-- Bottom: User Profile + Logout -->
        <div class="mt-auto border-t border-gray-100 dark:border-gray-800 pt-4">
            <div class="flex items-center gap-3 px-3 py-2 mb-3"
                :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'justify-center' : ''">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-300">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                    </svg>
                </div>
                <div x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                    class="overflow-hidden">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                        {{ auth()->user()->name ?? 'Wulan bin Fulan' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ auth()->user()->roles->first()->name ?? 'Admin' }}
                    </p>
                </div>
            </div>

            <div class="px-3"
                :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'flex justify-center' : ''">
                <form method="POST" action="{{ route('auth.logout') }}"
                    x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-brand-500 text-gray-800 dark:text-gray-200 text-sm font-medium hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors duration-150 bg-brand-50/50 dark:bg-transparent">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </button>
                </form>

                <form method="POST" action="{{ route('auth.logout') }}"
                    x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                    @csrf
                    <button type="submit"
                        class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-brand-500 text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors duration-150 bg-brand-50/50 dark:bg-transparent">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>