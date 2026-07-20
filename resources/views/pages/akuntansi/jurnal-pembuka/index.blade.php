@extends('layouts.app')
@section('title', 'Jurnal Pembuka')
@section('content')
@php
    $canEditJurnalPembuka   = auth()->user()->hasPermission('EDIT_JURNAL_PEMBUKA');
    $canDeleteJurnalPembuka = auth()->user()->hasPermission('DELETE_JURNAL_PEMBUKA');
    $bolehUbah = $jurnalPembuka->status === 'DRAFT';
@endphp
<div class="space-y-4 p-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Jurnal Pembuka</h1>
            @if($jurnalPembuka->periode)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Periode: <span class="font-medium">{{ $jurnalPembuka->periode->nama_periode }}</span>
                    <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                    {{ optional($jurnalPembuka->periode->tanggal_awal)->translatedFormat('d F Y') }}
                    s.d.
                    {{ optional($jurnalPembuka->periode->tanggal_akhir)->translatedFormat('d F Y') }}
                </p>
            @endif
        </div>

        {{-- Aksi header --}}
        <div class="flex items-center gap-2">
            @if($bolehUbah)
                @if($canEditJurnalPembuka)
                    <a href="{{ route('dashboard.jurnal-pembuka.edit', $jurnalPembuka) }}"
                       class="inline-flex items-center gap-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium px-4 py-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Edit
                    </a>
                @endif
                <button type="button" onclick="konfirmasiPosting({{ $jurnalPembuka->id }})"
                    class="inline-flex items-center gap-2 bg-green-600 text-white text-sm font-medium px-4 py-2 rounded-xl hover:bg-green-700 transition-colors">
                    Posting
                </button>
                @if($canDeleteJurnalPembuka)
                    <button type="button" onclick="hapusJurnal({{ $jurnalPembuka->id }})"
                        class="inline-flex items-center gap-2 border border-red-200 dark:border-red-900/50 text-red-600 text-sm font-medium px-4 py-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        Hapus
                    </button>
                @endif
            @else
                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    Sudah Diposting
                </span>
            @endif
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success')) <x-jurnal.alert type="success" :message="session('success')"/> @endif
    @if(session('error'))   <x-jurnal.alert type="error"   :message="session('error')"/>   @endif
    <div id="permissionAlertContainer"></div>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Kode Jurnal</p>
            <p class="text-lg font-semibold text-green-700 dark:text-green-400 mt-1">{{ $jurnalPembuka->kode_jurnal }}</p>
            <p class="text-xs text-gray-400 mt-1">Tanggal: {{ optional($jurnalPembuka->tanggal)->format('d M Y') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Debit / Kredit</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                Rp {{ number_format($jurnalPembuka->total_debit, 2, ',', '.') }}
                <span class="text-gray-400">/</span>
                Rp {{ number_format($jurnalPembuka->total_kredit, 2, ',', '.') }}
            </p>
            <p class="text-xs mt-1 {{ $jurnalPembuka->is_balance ? 'text-green-600' : 'text-amber-600' }}">
                {{ $jurnalPembuka->is_balance ? 'Seimbang' : 'Belum seimbang' }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
            @if($jurnalPembuka->status === 'POSTED')
                <span class="inline-flex mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">POSTED</span>
            @else
                <span class="inline-flex mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">DRAFT</span>
            @endif
        </div>
    </div>

    {{-- Detail saldo awal --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Detail Saldo Awal</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 text-left">
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 w-10">No</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500">Akun</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 text-center">Posisi</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 text-right">Debit</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach($jurnalPembuka->detailJurnal as $i => $d)
                    <tr>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $d->akun->kode_akun }} - {{ $d->akun->nama_akun }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex w-5 h-5 items-center justify-center rounded text-xs font-bold {{ $d->tipe === 'DEBIT' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">{{ $d->tipe === 'DEBIT' ? 'D' : 'K' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">{{ $d->tipe === 'DEBIT' ? 'Rp ' . number_format($d->nominal, 2, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-right text-green-700 dark:text-green-400">{{ $d->tipe === 'KREDIT' ? 'Rp ' . number_format($d->nominal, 2, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const CAN_DELETE_JURNAL_PEMBUKA = @json($canDeleteJurnalPembuka);

function showPageAlert(message) {
    const c = document.getElementById('permissionAlertContainer');
    if (!c) return;
    c.innerHTML = '<div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400 mb-4">' + message + '</div>';
    clearTimeout(c._t);
    c._t = setTimeout(function () { c.innerHTML = ''; }, 5000);
}

async function konfirmasiPosting(id) {
    if (!await confirmAsync('Yakin ingin memposting jurnal ini? Status tidak dapat dikembalikan ke Draft.', { title: 'Posting Jurnal', confirmLabel: 'Posting', confirmClass: 'bg-green-600 hover:bg-green-700' })) return;
    const res = await fetch('/dashboard/jurnal-pembuka/' + id + '/posting', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    const d = await res.json();
    if (d.success) window.location.reload(); else showPageAlert(d.message);
}

async function hapusJurnal(id) {
    if (!CAN_DELETE_JURNAL_PEMBUKA) { showPageAlert('Anda tidak memiliki akses untuk menghapus jurnal pembuka.'); return; }
    if (!await confirmAsync('Yakin ingin menghapus jurnal pembuka ini? Tindakan ini tidak dapat dibatalkan.', { confirmLabel: 'Hapus' })) return;
    const res = await fetch('/dashboard/jurnal-pembuka/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    const d = await res.json();
    if (d.success) window.location.href = '{{ route('dashboard.jurnal-pembuka.index') }}'; else showPageAlert(d.message);
}
</script>
@endpush