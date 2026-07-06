<x-modal id="deleteUserModal" title="Hapus Pengguna">

    <div class="px-6 py-5 space-y-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Yakin ingin menghapus pengguna <span class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</span>?
            Tindakan ini tidak dapat dibatalkan.
        </p>
    </div>

    <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
        <button type="button" onclick="closeModal('deleteUserModal')"
            class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            Batal
        </button>
        <button type="button"
            onclick="confirmDeleteUser({{ $user->id }})"
            class="px-5 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors">
            Ya, Hapus
        </button>
    </div>

</x-modal>

<script>
function confirmDeleteUser(id) {
    fetch(`{{ url('dashboard/users') }}/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: '_method=DELETE',
    })
    .then(r => r.json())
    .then(res => {
        closeModal('deleteUserModal');
        if (res.success) {
            showAlert(res.message, 'success');
            applyFilters();
        } else {
            showAlert(res.message, 'error');
        }
    })
    .catch(() => showAlert('Terjadi kesalahan.', 'error'));
}
</script>