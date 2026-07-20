<x-modal id="modal-edit-menu" title="Edit Menu">
    <form id="form-edit-menu" action="" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Nama Menu --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Menu <span class="text-red-500">*</span></label>
            <input type="text" name="menu_name" id="edit-menu_name"
                   class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
        </div>

        {{-- Icon (dropdown) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Icon</label>
            <input type="hidden" name="icon" id="edit-icon-input">
            <div class="relative">
                <button type="button" onclick="toggleIconPanel('edit')"
                    class="flex w-full items-center justify-between gap-2 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <span class="flex items-center gap-2">
                        <span id="edit-icon-preview" class="inline-flex h-6 w-6 items-center justify-center text-gray-600 dark:text-gray-300 [&>svg]:w-5 [&>svg]:h-5"></span>
                        <span id="edit-icon-label" class="text-gray-500 dark:text-gray-400">-- Pilih Icon --</span>
                    </span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="edit-icon-panel" class="hidden absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2 shadow-lg">
                    <div class="grid grid-cols-4 gap-1">
                        @foreach($availableIcons as $ic)
                            <button type="button" data-icon="{{ $ic }}" onclick="selectIcon('edit', this)"
                                class="flex flex-col items-center gap-1 rounded-lg p-2 text-gray-600 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600">
                                <span class="icon-svg [&>svg]:w-5 [&>svg]:h-5">{!! \App\Helpers\MenuHelper::getIconSvg($ic) !!}</span>
                                <span class="text-[10px] leading-tight text-center truncate w-full">{{ $ic }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Parent Menu --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Parent Menu</label>
            <select name="parent_id" id="edit-parent_id" onchange="toggleRouteField('edit', this.value)"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none appearance-none bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                <option value="">-- Tidak ada (menu utama/parent) --</option>
                @foreach($parentMenus as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->menu_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Route (hanya muncul untuk sub-menu) --}}
        <div id="edit-route-field" style="display:none">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Route Name</label>
            <select name="route_name" id="edit-route_name"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none appearance-none bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                <option value="">-- Pilih Route --</option>
                @foreach($availableRoutes as $routeName)
                    <option value="{{ $routeName }}">{{ $routeName }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-400">Pilih route yang akan dituju menu ini.</p>
        </div>

        {{-- Urutan + Status --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Urutan</label>
                <input type="number" name="sort_order" id="edit-sort_order" min="0"
                       class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            </div>

            {{-- Toggle Aktif --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
                <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="edit-is_active" value="1"
                           class="sr-only peer">
                    <div class="relative w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full
                                peer-checked:bg-green-500
                                after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                after:bg-white after:rounded-full after:h-5 after:w-5
                                after:transition-all
                                peer-checked:after:translate-x-5
                                transition-colors duration-200">
                    </div>
                    <span id="edit-is_active-label" class="text-sm text-gray-600 dark:text-gray-400">Nonaktif</span>
                </label>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="pt-1 flex items-center gap-3">
            <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">Update</button>
            <button type="button" onclick="closeModal('modal-edit-menu')" class="flex-1 text-center border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium px-6 py-2.5 rounded-xl transition-colors">Batal</button>
        </div>
    </form>
</x-modal>

<script>
    document.getElementById('edit-is_active').addEventListener('change', function () {
        document.getElementById('edit-is_active-label').textContent = this.checked ? 'Aktif' : 'Nonaktif';
    });
</script>