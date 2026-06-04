@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Back --}}
    <a href="{{ route('dashboard.kegiatan-panitia.index') }}"
    class="mb-5 inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900">

        {{-- icon arrow left --}}
        <svg xmlns="http://www.w3.org/2000/svg"
            width="18" height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">

            <path d="M15 18l-6-6 6-6"/>
        </svg>

        Kembali
    </a>

    {{-- Header Card --}}
    <div class="mb-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

        <div class="flex items-center justify-between">

            {{-- Left --}}
            <div class="flex items-center gap-4">

                @php
                    $bgColor = match($kegiatan->jenis_kegiatan) {
                        'QURBAN' => 'bg-red-100',
                        'ZAKAT'  => 'bg-green-100',
                        'KAJIAN' => 'bg-blue-100',
                        'SOSIAL' => 'bg-purple-100',
                        default  => 'bg-gray-100',
                    };

                    $statusColor = match($kegiatan->status) {
                        'BERJALAN' => 'bg-green-100 text-green-700',
                        'SELESAI' => 'bg-blue-100 text-blue-700',
                        'DRAFT' => 'bg-gray-100 text-gray-700',
                        'DIBATALKAN' => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp

                <div class="h-14 w-14 rounded-2xl {{ $bgColor }}"></div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ $kegiatan->nama_kegiatan }}
                    </h2>

                    <div class="mt-1 flex items-center gap-3 text-sm text-gray-500">
                        <span>
                            {{ $kegiatan->tanggal_mulai->format('j M Y') }}
                            –
                            {{ $kegiatan->tanggal_selesai?->format('j M Y') ?? '...' }}
                        </span>

                        <span>{{ ucfirst(strtolower($kegiatan->jenis_kegiatan)) }}</span>

                        <span>
                            Anggaran Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <span class="rounded-full px-4 py-1.5 text-sm font-medium {{ $statusColor }}">
                {{ ucfirst(strtolower($kegiatan->status)) }}
            </span>

        </div>
    </div>

    {{-- Table Card --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">

            <h3 class="text-lg font-semibold text-gray-900">
                Daftar transaksi
            </h3>

            @if($kegiatan->status === 'BERJALAN')
                @if(auth()->user()->hasPermission('CREATE_KEGIATAN'))
                    <a href="{{ route('dashboard.kegiatan-panitia.transaksi.create', $kegiatan) }}"
                       class="inline-flex items-center rounded-xl bg-green-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-green-700">
                        Catat transaksi
                    </a>
                @endif
            @endif

        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-gray-50">
                    <tr class="text-left text-sm text-gray-500">
                        <th class="px-6 py-4 font-medium">No</th>
                        <th class="px-6 py-4 font-medium">Kode</th>
                        <th class="px-6 py-4 font-medium">Tanggal</th>
                        <th class="px-6 py-4 font-medium">Jenis</th>
                        <th class="px-6 py-4 font-medium">Jumlah</th>
                        <th class="px-6 py-4 font-medium">Dompet</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 text-center font-medium">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($transaksi as $index => $item)

                        @php
                            $jenis = $item->kategoriTransaksi->jenis_transaksi;

                            $statusColor = match($item->status_approval) {
                                'PENDING'  => 'bg-yellow-100 text-yellow-700',
                                'APPROVED' => 'bg-green-100 text-green-700',
                                'REJECTED' => 'bg-red-100 text-red-700',
                                'REVISION' => 'bg-blue-100 text-blue-700',
                                default    => 'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <tr class="border-t border-gray-100 hover:bg-gray-50">

                            {{-- No --}}
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $transaksi->firstItem() + $index }}
                            </td>

                            {{-- Kode --}}
                            <td class="px-6 py-4 text-sm text-gray-700">
                                TRX-{{ now()->year }}-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $item->tanggal_transaksi->format('j M Y') }}
                            </td>

                            {{-- Jenis --}}
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-medium
                                    {{ $jenis === 'PEMASUKAN'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst(strtolower($jenis)) }}
                                </span>
                            </td>

                            {{-- Jumlah --}}
                            <td class="px-6 py-4 text-sm font-semibold
                                {{ $jenis === 'PEMASUKAN' ? 'text-green-600' : 'text-red-600' }}">

                                {{ $jenis === 'PEMASUKAN' ? '+' : '-' }}
                                Rp {{ number_format($item->jumlah, 0, ',', '.') }}

                            </td>

                            {{-- Dompet --}}
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $item->dompet->nama_dompet }}
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst(strtolower($item->status_approval)) }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">

                                    {{-- DETAIL --}}
                                    <a href="{{ route('dashboard.kegiatan-panitia.transaksi.show', [$kegiatan, $item]) }}"
                                       class="rounded-lg border border-gray-200 p-2 text-gray-500 hover:bg-gray-100"
                                       title="Detail">

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             width="18" height="18"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="2"
                                             stroke-linecap="round"
                                             stroke-linejoin="round">

                                            <path d="M2.062 12.348s3.75-7.348 9.938-7.348 9.938 7.348 9.938 7.348-3.75 7.348-9.938 7.348-9.938-7.348-9.938-7.348z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    {{-- EDIT --}}
                                    @if(in_array($item->status_approval, ['REVISION']))
                                        <a href="{{ route('dashboard.kegiatan-panitia.transaksi.edit', [$kegiatan, $item]) }}"
                                           class="rounded-lg border border-blue-200 p-2 text-blue-600 hover:bg-blue-50"
                                           title="Edit">

                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 width="18" height="18"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round">

                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- DELETE --}}
                                    @if($item->status_approval === 'PENDING')
                                        <form action="{{ route('dashboard.kegiatan-panitia.transaksi.destroy', [$kegiatan, $item]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin hapus transaksi ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="rounded-lg border border-red-200 p-2 text-red-600 hover:bg-red-50"
                                                    title="Hapus">

                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     width="18" height="18"
                                                     viewBox="0 0 24 24"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     stroke-width="2"
                                                     stroke-linecap="round"
                                                     stroke-linejoin="round">

                                                    <path d="M3 6h18"/>
                                                    <path d="M8 6V4h8v2"/>
                                                    <path d="M19 6l-1 14H6L5 6"/>
                                                </svg>

                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center text-sm text-gray-400">
                                Belum ada transaksi
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

    {{-- Pagination --}}
    @if($transaksi->hasPages())
        <div class="mt-5">
            {{ $transaksi->links() }}
        </div>
    @endif

</div>
@endsection