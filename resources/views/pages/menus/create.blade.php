@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Tambah Menu</h2>
    </div>

    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <form action="{{ route('dashboard.menus.store') }}" method="POST">
            @csrf

            {{-- Menu Name --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Nama Menu <span class="text-red-500">*</span>
                </label>
                <input type="text" name="menu_name" value="{{ old('menu_name') }}"
                       class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('menu_name') border-red-500 @enderror"
                       placeholder="Contoh: Keuangan">
                @error('menu_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Icon --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">Icon</label>
                <input type="text" name="icon" value="{{ old('icon') }}"
                       class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white"
                       placeholder="Contoh: dashboard">
            </div>

            {{-- Parent Menu --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Parent Menu
                </label>
                <select name="parent_id" id="parent_id"
                        class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white"
                        onchange="toggleRouteField(this.value)">
                    <option value="">-- Tidak ada (menu utama/parent) --</option>
                    @foreach($parentMenus as $parent)
                        <option value="{{ $parent->id }}"
                            {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->menu_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Route Name — hanya muncul kalau ada parent --}}
            <div class="mb-4" id="route_field"
                 style="{{ old('parent_id') ? '' : 'display:none' }}">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Route Name
                </label>
                <select name="route_name"
                        class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('route_name') border-red-500 @enderror">
                    <option value="">-- Pilih Route --</option>
                    @foreach($availableRoutes as $routeName)
                        <option value="{{ $routeName }}"
                            {{ old('route_name') == $routeName ? 'selected' : '' }}>
                            {{ $routeName }}
                        </option>
                    @endforeach
                </select>
                @error('route_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-body dark:text-bodydark">
                    Pilih route Laravel yang akan dituju menu ini
                </p>
            </div>

            {{-- Sort Order --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">Urutan</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                       class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white"
                       min="0">
            </div>

            {{-- Is Active --}}
            <div class="mb-6">
                <label class="flex items-center gap-2 text-sm font-medium text-black dark:text-white">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-stroke">
                    Aktif
                </label>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white hover:bg-opacity-90">
                    Simpan
                </button>
                <a href="{{ route('dashboard.menus.index') }}"
                   class="rounded-lg border border-stroke px-6 py-2.5 text-sm font-medium text-black hover:bg-gray-100 dark:text-white">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleRouteField(parentId) {
    const routeField = document.getElementById('route_field');
    if (parentId) {
        routeField.style.display = 'block';
    } else {
        routeField.style.display = 'none';
        routeField.querySelector('select').value = '';
    }
}
</script>
@endsection