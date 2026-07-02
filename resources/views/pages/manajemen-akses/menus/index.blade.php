@extends('layouts.app')
@section('title', 'Manajemen Menu')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Manajemen Menu</h1>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openModal('modal-tambah-menu')"
                class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                Tambah Menu
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div id="success-alert"
        class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 text-sm text-green-700 dark:text-green-400 transition-all duration-500">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div id="error-alert"
        class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400 transition-all duration-500">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Tabel --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
                <form method="GET" id="per-page-form">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="parent_menu" value="{{ request('parent_menu') }}">
                    <select name="per_page" onchange="document.getElementById('per-page-form').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        @foreach([10, 25, 50] as $n)
                        <option value="{{ $n }}" {{ ($perPage ?? 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </form>
                <span class="text-sm text-gray-500 dark:text-gray-400">entries</span>
            </div>

            <form method="GET" id="search-form" class="flex items-center gap-2">
                <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">

                {{-- Filter Status --}}
                <div class="relative">
                    <select name="status" onchange="document.getElementById('search-form').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 pr-8 appearance-none bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        <option value="">Semua Status</option>
                        <option value="aktif"       {{ request('status') === 'aktif'       ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ request('status') === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <div class="relative">
                    <select name="parent_menu" onchange="document.getElementById('search-form').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 pr-8 appearance-none bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        <option value="">Semua Menu</option>
                        <option value="none" {{ request('parent_menu') === 'none' ? 'selected' : '' }}>Hanya Parent</option>
                        @foreach($parentMenus as $parent)
                            <option value="{{ $parent->id }}" {{ request('parent_menu') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->menu_name }}
                            </option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Search --}}
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" id="search-input"
                        value="{{ request('search') }}"
                        placeholder="Search..."
                        class="pl-9 pr-8 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-56 placeholder-gray-400">
                    <button type="button" id="clear-search"
                        onclick="clearSearch()"
                        class="{{ request('search') ? '' : 'hidden' }} absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Nama Menu</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Icon</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Route</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Urutan</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($menus as $menu)

                        {{-- ── PARENT MENU (tanpa parent) ── --}}
                        @if(!$menu->parent_id)
                        <tr class="bg-green-700 dark:bg-green-800">
                            <td colspan="7" class="px-5 py-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-green-600/50 text-green-100 [&>svg]:w-4 [&>svg]:h-4">
                                            {!! \App\Helpers\MenuHelper::getIconSvg($menu->icon) !!}
                                        </span>
                                        <span class="text-sm font-semibold text-white">{{ $menu->menu_name }}</span>
                                        @if($menu->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-600/40 text-green-100">Aktif</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/40 text-red-100">Nonaktif</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('dashboard.menus.show', $menu) }}" title="Detail"
                                           class="p-1.5 text-green-200 hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <button type="button" title="Edit"
                                            data-menu="{{ json_encode([
                                                'update_url' => route('dashboard.menus.update', $menu),
                                                'id'         => $menu->id,
                                                'menu_name'  => $menu->menu_name,
                                                'icon'       => $menu->icon,
                                                'parent_id'  => $menu->parent_id,
                                                'route_name' => $menu->route_name,
                                                'sort_order' => $menu->sort_order,
                                                'is_active'  => (bool) $menu->is_active,
                                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                                            onclick="openEditMenuModal(this)"
                                            class="p-1.5 text-green-200 hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button type="button" title="Hapus"
                                            onclick="openConfirmModal({ id: 'confirm-delete', action: '{{ route('dashboard.menus.destroy', $menu) }}', title: 'Hapus Menu', message: 'Yakin ingin menghapus menu {{ $menu->menu_name }}?', confirmText: 'Hapus' })"
                                            class="p-1.5 text-green-200 hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        {{-- ── CHILD MENU (punya parent) ── --}}
                        @else
                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 pl-4">
                                    <div class="w-px h-4 bg-gray-200 dark:bg-gray-700"></div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ $menu->menu_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 [&>svg]:w-4 [&>svg]:h-4">
                                    {!! \App\Helpers\MenuHelper::getIconSvg($menu->icon) !!}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-400 dark:text-gray-500">
                                {{ $menu->route_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                {{ $menu->sort_order ?? 0 }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    {{ $menu->is_active
                                        ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400'
                                        : 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' }}">
                                    {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('dashboard.menus.show', $menu) }}" title="Detail"
                                       class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button type="button" title="Edit"
                                        data-menu="{{ json_encode([
                                            'update_url' => route('dashboard.menus.update', $menu),
                                            'id'         => $menu->id,
                                            'menu_name'  => $menu->menu_name,
                                            'icon'       => $menu->icon,
                                            'parent_id'  => $menu->parent_id,
                                            'route_name' => $menu->route_name,
                                            'sort_order' => $menu->sort_order,
                                            'is_active'  => (bool) $menu->is_active,
                                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                                        onclick="openEditMenuModal(this)"
                                        class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button" title="Hapus"
                                        onclick="openConfirmModal({ id: 'confirm-delete', action: '{{ route('dashboard.menus.destroy', $menu) }}', title: 'Hapus Menu', message: 'Yakin ingin menghapus menu {{ $menu->menu_name }}?', confirmText: 'Hapus' })"
                                        class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endif

                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                            Belum ada data menu.
                            <a href="#" onclick="openModal('modal-tambah-menu')" class="text-green-600 hover:underline ml-1">Tambah sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($menus->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">
            <div class="flex items-center gap-1">
                @if($menus->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
                @else
                <a href="{{ $menus->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
                @endif

                @foreach($menus->getUrlRange(1, $menus->lastPage()) as $page => $url)
                <a href="{{ $url }}"
                   class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                       {{ $page === $menus->currentPage()
                           ? 'bg-green-600 text-white font-medium'
                           : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @endforeach

                @if($menus->hasMorePages())
                <a href="{{ $menus->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
                @endif
            </div>
            <span class="text-xs text-gray-400 dark:text-gray-600">
                Showing {{ $menus->firstItem() }} to {{ $menus->lastItem() }} of {{ $menus->total() }} entries
            </span>
        </div>
        @endif

    </div>
</div>

{{-- Modals --}}
@include('pages.manajemen-akses.menus.create')
@include('pages.manajemen-akses.menus.edit')
<x-confirm-modal id="confirm-delete" />

@push('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }
    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.style.display = 'none';
        // Kosongkan form & hapus tampilan error saat modal ditutup.
        resetModalForm(modal);
    }

    // Kosongkan field & bersihkan UI error pada form di dalam sebuah modal.
    function resetModalForm(modal) {
        const form = modal.querySelector('form');
        if (!form || form.id === 'confirm-deleteForm') return;

        form.querySelectorAll('input, textarea').forEach(function (el) {
            if (['hidden', 'submit', 'button'].includes(el.type)) return;
            if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = el.defaultChecked;
            } else {
                el.value = '';
            }
        });
        form.querySelectorAll('select').forEach(function (sel) {
            sel.selectedIndex = 0;
        });

        form.querySelectorAll('p.text-red-500').forEach(function (p) { p.remove(); });
        form.querySelectorAll('.border-red-400').forEach(function (el) {
            el.classList.remove('border-red-400');
            el.classList.add('border-gray-200', 'dark:border-gray-700', 'focus:border-green-400');
        });
        const note = form.querySelector('.conn-error-notice');
        if (note) note.remove();

        // Reset tampilan icon picker, route field, & label status (khusus form menu).
        const scope = (modal.id === 'modal-edit-menu') ? 'edit' : 'create';
        if (document.getElementById(scope + '-icon-input')) {
            if (typeof setIconByName === 'function') setIconByName(scope, '');
            if (typeof toggleRouteField === 'function') toggleRouteField(scope, false);
            const act = document.getElementById(scope + '-is_active');
            const lbl = document.getElementById(scope + '-is_active-label');
            if (act && lbl) lbl.textContent = act.checked ? 'Aktif' : 'Nonaktif';
        }
    }

    function openConfirmModal(opts) {
        opts = opts || {};
        const id = opts.id || 'confirm-delete';
        const form = document.getElementById(id + 'Form');
        if (opts.action && form) form.action = opts.action;
        const titleEl = document.getElementById(id + '-title');
        const msgEl   = document.getElementById(id + '-message');
        if (titleEl && opts.title)   titleEl.textContent = opts.title;
        if (msgEl   && opts.message) msgEl.textContent   = opts.message;
        openModal(id);
    }

    // ===== Icon picker =====
    function toggleIconPanel(scope) {
        const panel = document.getElementById(scope + '-icon-panel');
        if (panel) panel.classList.toggle('hidden');
    }
    function selectIcon(scope, btn) {
        const name = btn.dataset.icon;
        const svg  = btn.querySelector('.icon-svg').innerHTML;
        document.getElementById(scope + '-icon-input').value = name;
        document.getElementById(scope + '-icon-preview').innerHTML = svg;
        document.getElementById(scope + '-icon-label').textContent = name;
        document.getElementById(scope + '-icon-panel').classList.add('hidden');
    }
    function setIconByName(scope, name) {
        const panel = document.getElementById(scope + '-icon-panel');
        if (!panel) return;
        const input   = document.getElementById(scope + '-icon-input');
        const preview = document.getElementById(scope + '-icon-preview');
        const label   = document.getElementById(scope + '-icon-label');
        if (input) input.value = name || '';
        const btn = name ? panel.querySelector('[data-icon="' + name + '"]') : null;
        if (btn) {
            preview.innerHTML = btn.querySelector('.icon-svg').innerHTML;
            label.textContent = name;
        } else {
            preview.innerHTML = '';
            label.textContent = '-- Pilih Icon --';
        }
    }

    // ===== Route field toggle =====
    function toggleRouteField(scope, hasParent) {
        const field = document.getElementById(scope + '-route-field');
        if (!field) return;
        field.style.display = hasParent ? 'block' : 'none';
        if (!hasParent) {
            const sel = field.querySelector('select');
            if (sel) sel.value = '';
        }
    }

    // ===== Populate modal edit =====
    function openEditMenuModal(el) {
        const data = JSON.parse(el.dataset.menu);
        document.getElementById('form-edit-menu').action = data.update_url;
        document.getElementById('edit-menu_name').value   = data.menu_name || '';
        document.getElementById('edit-sort_order').value  = (data.sort_order != null ? data.sort_order : 0);
        document.getElementById('edit-is_active').checked = !!data.is_active;

        const parentSel = document.getElementById('edit-parent_id');
        Array.from(parentSel.options).forEach(function (opt) {
            opt.disabled = (opt.value !== '' && parseInt(opt.value) === data.id);
        });
        parentSel.value = data.parent_id ? String(data.parent_id) : '';

        const routeSel = document.getElementById('edit-route_name');
        if (routeSel) routeSel.value = data.route_name || '';
        toggleRouteField('edit', !!data.parent_id);

        setIconByName('edit', data.icon);
        openModal('modal-edit-menu');
    }

    // ===== Search =====
    document.getElementById('search-input').addEventListener('input', function () {
        document.getElementById('clear-search').classList.toggle('hidden', this.value === '');
    });

    function clearSearch() {
        document.getElementById('search-input').value = '';
        document.getElementById('clear-search').classList.add('hidden');
        document.getElementById('search-form').submit();
    }

    // ===== Auto-dismiss alerts =====
    setTimeout(() => {
        const el = document.getElementById('success-alert');
        if (el) { el.classList.add('opacity-0'); setTimeout(() => el.remove(), 500); }
    }, 5000);
    setTimeout(() => {
        const el = document.getElementById('error-alert');
        if (el) { el.classList.add('opacity-0'); setTimeout(() => el.remove(), 500); }
    }, 5000);

    document.addEventListener('DOMContentLoaded', function () {
        setIconByName('create', @json(old('icon')));
        @if($errors->any())
            openModal('modal-tambah-menu');
        @endif

        // Cegah kehilangan data saat koneksi terputus (form tetap terisi).
        document
            .querySelectorAll('#modal-tambah-menu form, #form-edit-menu')
            .forEach(guardOfflineSubmit);
    });

    // ===== Cegah kehilangan data saat koneksi terputus =====
    function showConnNotice(form) {
        let note = form.querySelector('.conn-error-notice');
        if (!note) {
            note = document.createElement('div');
            note.className = 'conn-error-notice flex items-center gap-2 mb-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-3 py-2 text-xs text-red-600 dark:text-red-400';
            form.prepend(note);
        }
        note.textContent = 'Koneksi terputus. Data tidak terkirim dan tetap tersimpan di form — silakan coba lagi setelah koneksi pulih.';
    }

    function guardOfflineSubmit(form) {
        if (!form) return;
        form.addEventListener('submit', function (e) {
            if (!navigator.onLine) {
                e.preventDefault();
                showConnNotice(form);
            }
        });
        window.addEventListener('online', function () {
            const note = form.querySelector('.conn-error-notice');
            if (note) note.remove();
        });
    }
</script>
@endpush
@endsection