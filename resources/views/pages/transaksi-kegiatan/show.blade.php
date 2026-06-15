@extends('layouts.app')
@section('title', 'Detail Kegiatan')

@section('content')
<div class="p-6 space-y-6">

    {{-- Flash messages --}}
    @if(session('success'))
        <div id="success-alert" class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300 transition-opacity duration-500">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div id="error-alert" class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300 transition-opacity duration-500">{{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div id="warning-alert" class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 text-sm text-amber-700 dark:text-amber-300 transition-opacity duration-500"><?php echo session('warning'); ?></div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.transaksi-kegiatan.index') }}" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Kegiatan</h1>
        </div>
    </div>

    @php
        $statusColor = match($kegiatan->status) {
            'AKTIF'   => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
            'DITUTUP' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
            default   => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
        };
    @endphp

    {{-- Kegiatan Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $kegiatan->nama_kegiatan }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Anggaran: Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }} · Realisasi pemasukan: {{ $porsi }}%
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">{{ ucfirst(strtolower($kegiatan->status)) }}</span>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Daftar Transaksi</h3>
            <div class="flex items-center gap-2">
                <form method="GET" id="search-form">
                    <select name="jenis" onchange="this.form.submit()"
                            class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 pr-8 appearance-none bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        <option value="" {{ request('jenis') === '' ? 'selected' : '' }}>Semua Jenis</option>
                        <option value="PEMASUKAN" {{ request('jenis') === 'PEMASUKAN' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="PENGELUARAN" {{ request('jenis') === 'PENGELUARAN' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                    <select name="status" onchange="this.form.submit()"
                            class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 pr-8 appearance-none bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        <option value="" {{ request('status') === '' ? 'selected' : '' }}>Semua Status</option>
                        <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pending</option>
                        <option value="REVISION" {{ request('status') === 'REVISION' ? 'selected' : '' }}>Revisi</option>
                        <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved</option>
                        <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </form>
                @if($kegiatan->status === 'AKTIF')
                    <button type="button" onclick="openModal('modal-catat-transaksi')"
                            class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                        Catat Transaksi
                    </button>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase px-5 py-3 w-12">No</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Kode</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Tanggal</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase px-4 py-3">Jenis</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Jumlah</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Dompet</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase px-4 py-3">Status</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($transaksi as $index => $item)
                    @php
                        $jenis = $item->jenis_transaksi;
                        $kode  = 'TRX-' . $item->created_at->year . '-' . str_pad($item->id, 3, '0', STR_PAD_LEFT);
                        $statusBadge = match($item->status_approval) {
                            'PENDING'  => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
                            'APPROVED' => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                            'REJECTED' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                            'REVISION' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                            default    => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
                        };
                        $bisaUbah = $item->bisaDiedit() && $item->user_id === auth()->id();
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-5 py-3.5 text-center text-gray-500">{{ $transaksi->firstItem() + $index }}</td>
                        <td class="px-4 py-3.5 font-mono text-gray-700 dark:text-gray-300">{{ $kode }}</td>
                        <td class="px-4 py-3.5 text-gray-500">{{ $item->tanggal_transaksi->format('d M Y') }}</td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $jenis === 'PEMASUKAN' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">{{ ucfirst(strtolower($jenis)) }}</span>
                        </td>
                        <td class="px-4 py-3.5 font-semibold {{ $jenis === 'PEMASUKAN' ? 'text-green-600' : 'text-red-500' }}">{{ $jenis === 'PEMASUKAN' ? '+' : '-' }} Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5 text-gray-500">{{ $item->dompet->nama_dompet }}</td>
                        <td class="px-4 py-3.5 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">{{ ucfirst(strtolower($item->status_approval)) }}</span></td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Detail --}}
                                <a href="{{ route('dashboard.transaksi-kegiatan.transaksi.show', [$kegiatan, $item]) }}" title="Detail"
                                   class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>

                                @if($bisaUbah)
                                    {{-- Edit (buka modal & isi data) --}}
                                    <button type="button" title="Edit"
                                        data-transaksi="{{ json_encode([
                                            'kode_transaksi'        => $kode,
                                            'jenis_transaksi'       => $item->jenis_transaksi,
                                            'tanggal_transaksi'     => $item->tanggal_transaksi?->format('Y-m-d'),
                                            'jumlah'                => $item->jumlah,
                                            'dompet_id'             => $item->dompet_id,
                                            'kategori_transaksi_id' => $item->kategori_transaksi_id,
                                            'deskripsi'             => $item->deskripsi,
                                            'pencatat'              => $item->user->name,
                                            'bukti'                 => $item->buktiTransaksi->map(fn($b) => [
                                                'id'        => $b->id,
                                                'nama_file' => $b->nama_file ?? basename($b->path_file),
                                                'url'       => Storage::url($b->path_file),
                                            ])->values(),
                                            'update_url' => route('dashboard.transaksi-kegiatan.transaksi.update', [$kegiatan, $item]),
                                        ]) }}"
                                        onclick="openEditModal(this)"
                                        class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    {{-- Delete via modal konfirmasi --}}
                                    <button type="button" title="Hapus"
                                        onclick="openConfirmModal({ id: 'confirm-delete', action: '{{ route('dashboard.transaksi-kegiatan.transaksi.destroy', [$kegiatan, $item]) }}', title: 'Hapus Transaksi', message: 'Yakin ingin menghapus transaksi {{ $kode }}? Tindakan ini tidak dapat dibatalkan.', confirmText: 'Hapus' })"
                                        class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">Belum ada transaksi kegiatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transaksi->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800">{{ $transaksi->links() }}</div>
        @endif
    </div>
</div>

{{-- Modals --}}
@include('pages.transaksi-kegiatan.create-transaksi')
@include('pages.transaksi-kegiatan.edit-transaksi')
<x-confirm-modal id="confirm-delete" />

@push('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    // Modal konfirmasi generik (delete & aksi lain)
    function openConfirmModal(opts) {
        opts = opts || {};
        const id = opts.id || 'confirm-delete';

        const form = document.getElementById(id + 'Form');
        if (opts.action && form) form.action = opts.action;

        if (form && opts.method) {
            let methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.value = opts.method;
        }
        const modal = document.getElementById(id);
        if (modal) {
            const titleEl = modal.querySelector('h3');
            const msgEl   = modal.querySelector('p');
            const btnEl   = modal.querySelector('button[type="submit"]');
            if (titleEl && opts.title)       titleEl.textContent = opts.title;
            if (msgEl   && opts.message)     msgEl.textContent   = opts.message;
            if (btnEl   && opts.confirmText) btnEl.textContent   = opts.confirmText;
        }

        openModal(id);
    }

    function updateEditToggleStyle(jenis) {
        const a = document.getElementById('edit-btn-pemasukan');
        const b = document.getElementById('edit-btn-pengeluaran');
        if (!a || !b) return;
        if (jenis === 'PEMASUKAN') {
            a.classList.add('bg-green-600', 'text-white'); a.classList.remove('text-gray-500');
            b.classList.remove('bg-green-600', 'text-white'); b.classList.add('text-gray-500');
        } else {
            b.classList.add('bg-green-600', 'text-white'); b.classList.remove('text-gray-500');
            a.classList.remove('bg-green-600', 'text-white'); a.classList.add('text-gray-500');
        }
    }

    function showEditFileNames(input) {
        const label = document.getElementById('editFileLabel');
        if (input.files.length > 0 && label) {
            const names = Array.from(input.files).map(function (f) { return f.name; }).join(', ');
            label.innerHTML = '<p class="text-sm font-medium text-gray-900 dark:text-white">' + names + '</p>';
        }
    }

    function openEditModal(el) {
        const data = JSON.parse(el.dataset.transaksi);

        document.getElementById('form-edit-transaksi').action = data.update_url;
        document.getElementById('edit-kode').textContent     = data.kode_transaksi;
        document.getElementById('edit-pencatat').textContent = data.pencatat;

        // set jenis + filter kategori DULU, baru isi value kategori
        const radio = document.querySelector('#form-edit-transaksi input[name="jenis_transaksi"][value="' + data.jenis_transaksi + '"]');
        if (radio) radio.checked = true;
        updateEditToggleStyle(data.jenis_transaksi);

        document.getElementById('edit-tanggal').value   = data.tanggal_transaksi;
        document.getElementById('edit-jumlah').value    = data.jumlah;
        document.getElementById('edit-deskripsi').value = data.deskripsi || '';
        document.getElementById('edit-dompet').value    = data.dompet_id;
        document.getElementById('edit-kategori').value  = data.kategori_transaksi_id;

        const existingPicker = document.getElementById('edit-tanggal')._flatpickr;
        if (existingPicker) existingPicker.destroy();

        flatpickr('#edit-tanggal', {
            dateFormat: 'Y-m-d',
            allowInput: true,
            defaultDate: data.tanggal_transaksi || null,
        });
        // Bukti lama
        const buktiList = document.getElementById('edit-bukti-list');
        const buktiHint = document.getElementById('edit-bukti-hint');
        buktiList.innerHTML = '';

        if (data.bukti && data.bukti.length > 0) {
            buktiHint.classList.remove('hidden');
            data.bukti.forEach(function (b) {
                buktiList.insertAdjacentHTML('beforeend',
                    '<div class="bukti-item flex items-center gap-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2">' +
                        '<a href="' + b.url + '" target="_blank" class="bukti-name text-xs text-gray-600 dark:text-gray-300 hover:text-green-600 truncate max-w-[140px]">' + b.nama_file + '</a>' +
                        '<label class="cursor-pointer ml-1 select-none" title="Tandai untuk dihapus">' +
                            '<input type="checkbox" name="hapus_bukti[]" value="' + b.id + '" class="sr-only" onchange="toggleHapusBukti(this)">' +
                            '<span class="hapus-x text-gray-400 hover:text-red-500 transition-colors text-sm font-semibold">✕</span>' +
                        '</label>' +
                    '</div>');
            });
        } else {
            buktiHint.classList.add('hidden');
        }

        // reset input file
        document.getElementById('editBuktiInput').value = '';
        document.getElementById('editFileLabel').innerHTML =
            '<p class="text-sm text-gray-500 dark:text-gray-400">Klik untuk upload foto atau PDF baru</p>' +
            '<p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Maks. 5MB · JPG, PNG, PDF</p>';

        openModal('modal-edit-transaksi');
    }

    function toggleHapusBukti(input) {
        const item = input.closest('.bukti-item');
        if (!item) return;
        const name = item.querySelector('.bukti-name');
        const mark = item.querySelector('.hapus-x');
        if (input.checked) {
            item.classList.add('opacity-60');
            if (name) name.classList.add('line-through', 'text-red-500');
            if (mark) mark.classList.add('text-red-600');
        } else {
            item.classList.remove('opacity-60');
            if (name) name.classList.remove('line-through', 'text-red-500');
            if (mark) mark.classList.remove('text-red-600');
        }
    }

    function validateAndSubmitEdit() {
        const form = document.getElementById('form-edit-transaksi');
        if (!form.getAttribute('action')) {        
            alert('Form edit belum siap. Tutup modal lalu klik Edit lagi.');
            return;
        }
    
        let valid = true;

        // Tanggal
        const tanggal    = document.getElementById('edit-tanggal');
        const tanggalErr = document.getElementById('edit-tanggal-error');
        if (!tanggal.value) {
            tanggal.classList.add('border-red-400');
            tanggal.classList.remove('border-gray-200');
            tanggalErr.classList.remove('hidden');
            valid = false;
        } else {
            tanggal.classList.remove('border-red-400');
            tanggalErr.classList.add('hidden');
        }

        // Dompet
        const dompet    = document.getElementById('edit-dompet');
        const dompetErr = document.getElementById('edit-dompet-error');
        if (!dompet.value) {
            dompet.classList.add('border-red-400');
            dompet.classList.remove('border-gray-200');
            dompetErr.classList.remove('hidden');
            valid = false;
        } else {
            dompet.classList.remove('border-red-400');
            dompetErr.classList.add('hidden');
        }

        // Kategori
        const kategori    = document.getElementById('edit-kategori');
        const kategoriErr = document.getElementById('edit-kategori-error');
        if (!kategori.value) {
            kategori.classList.add('border-red-400');
            kategori.classList.remove('border-gray-200');
            kategoriErr.classList.remove('hidden');
            valid = false;
        } else {
            kategori.classList.remove('border-red-400');
            kategoriErr.classList.add('hidden');
        }

        if (valid) {
            form.submit();
        }
    }

    // Auto-hide flash message (dengan guard, tidak error jika elemen tak ada)
    document.addEventListener('DOMContentLoaded', function () {
        ['success-alert', 'error-alert', 'warning-alert'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            setTimeout(function () {
                el.classList.add('opacity-0');
                setTimeout(function () { el.remove(); }, 500);
            }, 5000);
        });
    });
</script>
@endpush
@endsection
