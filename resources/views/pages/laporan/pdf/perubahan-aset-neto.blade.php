@extends('pages.laporan.pdf._layout')
@section('judul', 'Laporan Perubahan Aset Neto')
@section('subjudul', 'Untuk Periode yang Berakhir ' . ($periode ? $periode->tanggal_akhir->translatedFormat('d F Y') : '—'))
@section('isi')
<table class="fin">
    <thead>
        <tr>
            <th class="text-left" style="width:50%;">Uraian</th>
            <th class="text-right">{{ $periode?->nama_periode ?? 'Periode Ini' }}</th>
            <th class="text-right">{{ $periodePrev?->nama_periode ?? 'Periode Lalu' }}</th>
        </tr>
    </thead>
    <tbody>
        <tr class="sec"><td colspan="3">Aset Neto Tanpa Pembatasan</td></tr>
        <tr><td class="indent">Saldo Awal Periode</td><td class="text-right">{{ pdfFmt($data['saldoAwalTanpa'] ?? 0) }}</td><td class="text-right muted">{{ pdfPrev($dataPrev, 'saldoAwalTanpa') }}</td></tr>
        <tr><td class="indent">Surplus (Defisit) Periode Berjalan</td><td class="text-right">{{ pdfSigned($data['surplusTanpa'] ?? 0) }}</td><td class="text-right muted">{{ pdfPrev($dataPrev, 'surplusTanpa') }}</td></tr>
        @if(($data['pkl'] ?? 0) != 0)
        <tr><td class="indent">Penghasilan Komprehensif Lain</td><td class="text-right">{{ pdfSigned($data['pkl'] ?? 0) }}</td><td class="text-right muted">{{ pdfPrev($dataPrev, 'pkl') }}</td></tr>
        @endif
        <tr><td class="indent">Aset Neto yang Dibebaskan dari Pembatasan</td><td class="text-right">{{ pdfFmt($data['dibebaskan'] ?? 0) }}</td><td class="text-right muted">{{ pdfPrev($dataPrev, 'dibebaskan') }}</td></tr>
        <tr class="subtotal"><td class="indent">Saldo Akhir Periode</td><td class="text-right">{{ pdfSigned($data['saldoAkhirTanpa'] ?? 0) }}</td><td class="text-right">{{ pdfPrev($dataPrev, 'saldoAkhirTanpa') }}</td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="sec"><td colspan="3">Aset Neto Dengan Pembatasan</td></tr>
        <tr><td class="indent">Saldo Awal Periode</td><td class="text-right">{{ pdfFmt($data['saldoAwalDengan'] ?? 0) }}</td><td class="text-right muted">{{ pdfPrev($dataPrev, 'saldoAwalDengan') }}</td></tr>
        <tr><td class="indent">Surplus (Defisit) Periode Berjalan</td><td class="text-right">{{ pdfSigned($data['surplusDengan'] ?? 0) }}</td><td class="text-right muted">{{ pdfPrev($dataPrev, 'surplusDengan') }}</td></tr>
        <tr><td class="indent">Aset Neto yang Dibebaskan dari Pembatasan</td><td class="text-right red">({{ pdfFmt($data['dibebaskan'] ?? 0) }})</td><td class="text-right muted">@php $db = $dataPrev['dibebaskan'] ?? 0; @endphp @if($dataPrev && $db != 0)({{ pdfFmt($db) }})@else—@endif</td></tr>
        <tr class="subtotal"><td class="indent">Saldo Akhir Periode</td><td class="text-right">{{ pdfSigned($data['saldoAkhirDengan'] ?? 0) }}</td><td class="text-right">{{ pdfPrev($dataPrev, 'saldoAkhirDengan') }}</td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="total-green"><td>Jumlah Aset Neto Akhir Periode</td><td class="text-right">{{ pdfSigned($data['totalSaldoAkhir'] ?? 0) }}</td><td class="text-right green2">{{ pdfPrev($dataPrev, 'totalSaldoAkhir') }}</td></tr>
    </tbody>
</table>
<div class="note-box">
    <p><strong>Catatan:</strong> Aset neto tanpa pembatasan mencerminkan sumber daya yang dapat digunakan secara bebas oleh entitas untuk mendukung kegiatan operasional. Aset neto dengan pembatasan mencerminkan sumber daya yang penggunaannya dibatasi oleh pemberi sumber daya untuk tujuan tertentu.</p>
</div>
@endsection
