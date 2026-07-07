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
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
        @forelse($users as $index => $user)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
            <td class="px-5 py-3.5 text-center text-gray-500 dark:text-gray-400">
                {{ $users->firstItem() + $index }}
            </td>
            <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200">{{ $user->name }}</td>
            <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">{{ explode('@', $user->email)[0] }}</td>
            <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
            <td class="px-4 py-3.5 text-center">
                @if($user->status === 'active')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Aktif</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400">Tidak Aktif</span>
                @endif
            </td>
            <td class="px-4 py-3.5">
                @if($user->roles)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                    {{ $user->roles->role_name }}
                </span>
                @endif
            </td>
            <td class="px-5 py-3.5">
                <div class="flex items-center justify-center gap-1">
                    @php $kredensialSent = $user->credentials_sent_at; @endphp
                    <button onclick="sendCredentials({{$user->id}})"
                        title="{{$kredensialSent ? 'Kredensial sudah dikirim ' . $kredensialSent->format('d M Y H:i') . ' - klik untuk kirim ulang' : 'Kirim kredensial ke user (belum dikirim)' }}"
                        class="p-1.5 rounded-lg transition-colors {{$kredensialSent ? 'text-gray-300 dark:text-gray-600 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700' : 'text-yellow-500 hover:text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/20' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </button>
                    <button onclick="openEditModal({{ $user->id }})"
                        class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="openDeleteModal({{ $user->id }})"
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
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>
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