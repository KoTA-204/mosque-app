<x-confirm-modal
    id="deleteUserModal"
    title="Hapus User"
    message="Yakin ingin menghapus user {{ $user->name }}? Tindakan ini tidak dapat dibatalkan."
>
    <x-slot name="actions">
        <button type="button" onclick="closeModal('deleteUserModal')"
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
            Batal
        </button>
        <button type="button"
            onclick="submitDeleteUser('deleteUserModal', '{{ route('dashboard.users.destroy', $user->id) }}')"
            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
            Hapus
        </button>
    </x-slot>
</x-confirm-modal>