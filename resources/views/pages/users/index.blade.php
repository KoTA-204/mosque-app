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

            {{-- Search --}}
            <form method="GET" action="{{ route('dashboard.users.index') }}" class="relative" id="searchForm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" id="filterSearch"
                    value="{{ request('search') }}"
                    placeholder="Search nama / email..."
                    autocomplete="off"
                    class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-56 placeholder-gray-400">
            </form>

            {{-- Filter Role & Status --}}
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

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3 w-12">No</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Nama</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Username</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Email</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Role</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($users as $index => $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors table-row"
                    data-nama="{{ strtolower($user->name) }}"
                    data-role="{{ $user->roles->pluck('role_name')->join(',') }}"
                    data-status="{{ $user->status }}">
                    <td class="px-5 py-3.5 text-center text-gray-500 dark:text-gray-400">
                        {{ $users->firstItem() + $index }}
                    </td>
                    <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200">{{ $user->name }}</td>
                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">{{ explode('@', $user->email)[0] }}</td>
                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                    <td class="px-4 py-3.5 text-center">
                        @if($user->status == 'active')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex flex-wrap gap-1">
                            @foreach($user->roles as $role)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                                {{ $role->role_name }}
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1">
                            <button onclick='openEditModal(@json($user), @json($roles))'
                                class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                        Tidak ada data user.
                        <button onclick="openCreateModal()" class="text-green-600 hover:underline ml-1">Tambah sekarang</button>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div id="emptyFilter" class="hidden px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
            Tidak ada data yang sesuai filter.
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">
            <div class="flex items-center gap-1">
                @if($users->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
                @else
                <a href="{{ $users->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
                @endif

                @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors {{ $page === $users->currentPage() ? 'bg-green-600 text-white font-medium' : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @endforeach

                @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
                @endif
            </div>
            <span class="text-xs text-gray-400 dark:text-gray-600">
                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
            </span>
        </div>
        @endif
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

function filterRows(role, status) {
    const rows = document.querySelectorAll('#tableBody .table-row');
    let visible = 0;

    rows.forEach(row => {
        const rowRoles  = (row.dataset.role || '').split(',').map(r => r.trim()).filter(r => r !== '');
        const rowStatus = (row.dataset.status || '').trim();

        const matchRole   = !role   || rowRoles.includes(role);
        const matchStatus = !status || rowStatus === status;

        const show = matchRole && matchStatus;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('emptyFilter').classList.toggle('hidden', visible > 0);
}

document.getElementById('filterRole').addEventListener('change', function() {
    filterRows(this.value.trim(), document.getElementById('filterStatus').value.trim());
});

document.getElementById('filterStatus').addEventListener('change', function() {
    filterRows(document.getElementById('filterRole').value.trim(), this.value.trim());
});

let searchDebounce;
document.getElementById('filterSearch').addEventListener('input', () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        document.getElementById('searchForm').submit();
    }, 500);
});
</script>
@endsection