@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">User Management</h1>
        <button onclick="openCreateModal()"
            class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
            Tambah User
        </button>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 text-sm text-green-700 dark:text-green-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="filterSearch"
                    placeholder="Search nama / email..."
                    autocomplete="off"
                    class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-56 placeholder-gray-400">
            </div>

            <div class="flex items-center gap-2">
                <select id="filterRole"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->role_name }}">{{ $role->role_name }}</option>
                    @endforeach
                </select>
                <select id="filterStatus"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
            </div>
        </div>

        <div id="tableWrapper">
            @include('pages.users.table')
        </div>

    </div>

</div>

{{-- Modal Backdrop --}}
<div id="modalBackdrop" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-40 flex items-center justify-center p-4"
     onclick="closeAllModals(event)">
    @include('pages.users.create')
    @include('pages.users.edit')
    @include('pages.users.delete')
</div>

<script>
const backdrop    = document.getElementById('modalBackdrop');
const editModal   = document.getElementById('editModal');
const deleteModal = document.getElementById('deleteModal');
const createModal = document.getElementById('createModal');

function showBackdrop() { backdrop.classList.remove('hidden'); }
function hideBackdrop() { backdrop.classList.add('hidden'); }

function closeAllModals(e) {
    if (e && e.target !== backdrop) return;
    [editModal, deleteModal, createModal].forEach(m => m.classList.add('hidden'));
    hideBackdrop();
}

function openModal(modal) {
    [editModal, deleteModal, createModal].forEach(m => m.classList.add('hidden'));
    modal.classList.remove('hidden');
    showBackdrop();
}

function openEditModal(user, roles) {
    const route = "{{ route('dashboard.users.update', ':id') }}".replace(':id', user.id);
    document.getElementById('editForm').action     = route;
    document.getElementById('edit_name').value     = user.name;
    document.getElementById('edit_username').value = user.email.split('@')[0];
    document.getElementById('edit_email').value    = user.email;
    document.getElementById('edit_status').value   = user.status;

    const sel        = document.getElementById('edit_role_id');
    const userRoleId = user.roles && user.roles.length ? user.roles[0].id : null;
    sel.innerHTML    = roles.map(r =>
        `<option value="${r.id}" ${r.id == userRoleId ? 'selected' : ''}>${r.role_name}</option>`
    ).join('');

    openModal(editModal);
}

function openDeleteModal(id, nama) {
    const route = "{{ route('dashboard.users.destroy', ':id') }}".replace(':id', id);
    document.getElementById('deleteForm').action       = route;
    document.getElementById('delete_nama').textContent = nama;
    openModal(deleteModal);
}

function openCreateModal() { openModal(createModal); }

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        [editModal, deleteModal, createModal].forEach(m => m.classList.add('hidden'));
        hideBackdrop();
    }
});

// Auto-open create modal jika ada validation error
@if($errors->any())
document.addEventListener('DOMContentLoaded', () => openCreateModal());
@endif

// ── AJAX FILTER ──────────────────────────────────────────────
const filterUrl = "{{ route('dashboard.users.index') }}";
let filterDebounce;

function applyFilters() {
    const search = document.getElementById('filterSearch').value;
    const role   = document.getElementById('filterRole').value;
    const status = document.getElementById('filterStatus').value;

    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (role)   params.set('role', role);
    if (status) params.set('status', status);

    fetch(`${filterUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
    });
}

document.getElementById('filterSearch').addEventListener('input', () => {
    clearTimeout(filterDebounce);
    filterDebounce = setTimeout(applyFilters, 400);
});

document.getElementById('filterRole').addEventListener('change', applyFilters);
document.getElementById('filterStatus').addEventListener('change', applyFilters);
</script>
@endsection