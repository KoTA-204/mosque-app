@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Breadcrumb --}}
    <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('dashboard.kegiatan.index') }}" class="hover:text-primary">Kegiatan</a>
        <span>/</span>
        <a href="{{ route('dashboard.kegiatan.show', $kegiatan) }}" class="hover:text-primary">{{ $kegiatan->nama_kegiatan }}</a>
        <span>/</span>
        <span class="text-black dark:text-white">Edit</span>
    </nav>

    <div class="rounded-xl border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">

        {{-- Card Header --}}
        <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
            <h3 class="text-lg font-semibold text-black dark:text-white">Edit Kegiatan</h3>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Perbarui informasi kegiatan {{ $kegiatan->nama_kegiatan }}.</p>
        </div>

        {{-- Form --}}
        <form action="{{ route('dashboard.kegiatan.update', $kegiatan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="px-6 py-6">
                @include('dashboard.kegiatan._form')
            </div>

            <div class="border-t border-stroke px-6 py-4 dark:border-strokedark flex items-center justify-end gap-3">
                <a href="{{ route('dashboard.kegiatan.show', $kegiatan) }}"
                   class="rounded-lg border border-stroke px-5 py-2.5 text-sm font-medium text-black hover:bg-gray-100 dark:border-strokedark dark:text-white dark:hover:bg-meta-4">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection