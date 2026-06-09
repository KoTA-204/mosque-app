@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">User Management</h1>
        <button onclick="openCreateModal()"
            class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
            + Tambah User
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total User</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">User Aktif</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['aktif'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">User Tidak Aktif</p>
            <p class="text-2xl font-bold text-red-500">{{ $stats['tidak_aktif'] }}</p>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                Show
                <select id="perPage" onchange="applyFilters()"
                    class="border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                entries
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <select id="filterRole" onchange="applyFilters()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->role_name }}">{{ $role->role_name }}</option>
                    @endforeach
                </select>
                <select id="filterStatus" onchange="applyFilters()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="filterSearch" placeholder="Search..." autocomplete="off"
                        class="pl-9 pr-4 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-48 placeholder-gray-400">
                </div>
            </div>
        </div>

        <div id="tableWrapper">
            @include('pages.users.table')
        </div>
    </div>
</div>

{{-- Modal Container --}}
<div id="modalContainer"></div>

{{-- Toast --}}
<div id="toast" class="hidden fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium"></div>

<script>
const modalContainer = document.getElementById('modalContainer');
const csrfToken      = document.querySelector('meta[name="csrf-token"]').content;
const baseUrl        = "{{ url('dashboard/users') }}";
const filterUrl      = "{{ route('dashboard.users.index') }}";
let filterDebounce;

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}

function closeModal(id) {
    if (id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
            if (modalContainer.contains(el)) modalContainer.innerHTML = '';
        }
    } else {
        modalContainer.querySelectorAll('[id$="Modal"]').forEach(el => el.style.display = 'none');
        modalContainer.innerHTML = '';
    }
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        modalContainer.querySelectorAll('[id$="Modal"]').forEach(el => {
            if (el.style.display === 'flex') closeModal(el.id);
        });
    }
});

function loadModal(url) {
    modalContainer.innerHTML = '';
    const loader = document.createElement('div');
    loader.style.cssText = 'display:flex;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);';
    loader.innerHTML = `
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-10 flex items-center justify-center">
            <svg class="animate-spin w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
        </div>`;
    document.body.appendChild(loader);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            loader.remove();
            modalContainer.innerHTML = data.html;
            modalContainer.querySelectorAll('script').forEach(old => {
                const s = document.createElement('script');
                old.src ? (s.src = old.src) : (s.textContent = old.textContent);
                document.head.appendChild(s);
                old.remove();
            });
            const modal = modalContainer.querySelector('[id$="Modal"]');
            if (modal) modal.style.display = 'flex';
        })
        .catch(() => showToast('Gagal memuat data.', 'error'));
}

function openCreateModal() { loadModal(`${baseUrl}/create`); }
function openEditModal(id)  { loadModal(`${baseUrl}/${id}/edit`); }
function openDeleteModal(id){ loadModal(`${baseUrl}/${id}/delete`); }

function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    toast.className = `fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium ${
        type === 'success'
            ? 'bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-400'
            : 'bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400'
    }`;
    toast.innerHTML = msg;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3500);
}

function submitUserForm(formId, method, url) {
    const form = document.getElementById(formId);
    form.querySelectorAll('[id^="err-"]').forEach(el => el.textContent = '');
    form.querySelectorAll('input,select,textarea').forEach(el => el.classList.remove('border-red-400'));

    const data = new FormData(form);
    if (method === 'PUT') data.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const active = modalContainer.querySelector('[id$="Modal"]');
            if (active) closeModal(active.id);
            showToast(res.message, 'success');
            applyFilters();
            fetchStats();
        } else if (res.errors) {
            Object.entries(res.errors).forEach(([field, messages]) => {
                const el    = form.querySelector(`[name="${field}"]`);
                const errEl = document.getElementById(`err-${field}`);
                if (el)    el.classList.add('border-red-400');
                if (errEl) errEl.textContent = messages[0];
            });
        }
    })
    .catch(() => showToast('Terjadi kesalahan.', 'error'));
}

function submitDeleteUser(formId, url) {
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN':     csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type':     'application/x-www-form-urlencoded',
        },
        body: '_method=DELETE',
    })
    .then(r => r.json())
    .then(res => {
        const active = modalContainer.querySelector('[id$="Modal"]');
        if (res.success) {
            if (active) closeModal(active.id);
            showToast(res.message, 'success');
            applyFilters();
            fetchStats();
        } else {
            showToast(res.message ?? 'Gagal menghapus.', 'error');
        }
    })
    .catch(() => showToast('Terjadi kesalahan.', 'error'));
}

// ── Refresh stats cards setelah operasi ──────────────────────
function fetchStats() {
    fetch(`${filterUrl}?stats_only=1`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.stats) {
            document.querySelector('.stat-total').textContent      = data.stats.total;
            document.querySelector('.stat-aktif').textContent      = data.stats.aktif;
            document.querySelector('.stat-tidak-aktif').textContent = data.stats.tidak_aktif;
        }
    });
}

function applyFilters() {
    const params = new URLSearchParams();
    const search  = document.getElementById('filterSearch').value;
    const role    = document.getElementById('filterRole').value;
    const status  = document.getElementById('filterStatus').value;
    const perPage = document.getElementById('perPage').value;

    if (search)  params.set('search',   search);
    if (role)    params.set('role',     role);
    if (status)  params.set('status',   status);
    params.set('per_page', perPage);

    fetch(`${filterUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
        if (data.stats) {
            document.querySelector('.stat-total').textContent       = data.stats.total;
            document.querySelector('.stat-aktif').textContent       = data.stats.aktif;
            document.querySelector('.stat-tidak-aktif').textContent = data.stats.tidak_aktif;
        }
    });
}

document.getElementById('filterSearch').addEventListener('input', () => {
    clearTimeout(filterDebounce);
    filterDebounce = setTimeout(applyFilters, 400);
});
</script>
@endsection