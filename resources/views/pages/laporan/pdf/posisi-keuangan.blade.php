@extends('pages.laporan.pdf._layout')
@section('judul', 'Laporan Posisi Keuangan')
@section('subjudul', 'Per ' . ($periode ? $periode->tanggal_akhir->translatedFormat('d F Y') : '—'))
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
        <tr class="sec"><td colspan="3">Aset</td></tr>
        <tr class="subsec"><td colspan="3">Aset Lancar</td></tr>
        @forelse($data['rincianAsetLancar'] ?? [] as $row)
        <tr>
            <td class="indent">{{ $row->nama_akun }}</td>
            <td class="text-right">{{ pdfFmt($row->saldo) }}</td>
            <td class="text-right muted">@php $pr = collect($dataPrev['rincianAsetLancar'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp {{ $pr ? pdfFmt($pr->saldo) : '—' }}</td>
        </tr>
        @empty
        <tr><td class="indent muted italic" colspan="3">Tidak ada aset lancar</td></tr>
        @endforelse
        <tr class="subtotal"><td class="indent">Jumlah Aset Lancar</td><td class="text-right">{{ pdfFmt($data['jumlahAsetLancar'] ?? 0) }}</td><td class="text-right">{{ pdfFmt($dataPrev['jumlahAsetLancar'] ?? 0) }}</td></tr>
        <tr class="subsec"><td colspan="3">Aset Tetap</td></tr>
        @forelse($data['rincianAsetTetap'] ?? [] as $row)
        <tr>
            <td class="{{ $row->is_akumulasi ? 'indent2 italic muted' : 'indent' }}">{{ $row->nama_akun }}</td>
            <td class="text-right {{ $row->is_akumulasi ? 'red' : '' }}">{{ $row->is_akumulasi ? '(' . pdfFmt($row->saldo) . ')' : pdfFmt($row->saldo) }}</td>
            <td class="text-right muted">@php $pr = collect($dataPrev['rincianAsetTetap'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp @if($pr){{ $pr->is_akumulasi ? '(' . pdfFmt($pr->saldo) . ')' : pdfFmt($pr->saldo) }}@else—@endif</td>
        </tr>
        @empty
        <tr><td class="indent muted italic" colspan="3">Tidak ada aset tetap</td></tr>
        @endforelse
        <tr class="subtotal"><td class="indent">Jumlah Aset Tetap</td><td class="text-right">{{ pdfFmt($data['jumlahAsetTetap'] ?? 0) }}</td><td class="text-right">{{ pdfFmt($dataPrev['jumlahAsetTetap'] ?? 0) }}</td></tr>
        <tr class="total-green"><td>Jumlah Aset</td><td class="text-right">{{ pdfFmt($data['jumlahAset'] ?? 0) }}</td><td class="text-right green2">{{ pdfFmt($dataPrev['jumlahAset'] ?? 0) }}</td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="sec"><td colspan="3">Liabilitas</td></tr>
        @foreach($data['grupLiabilitas'] ?? [] as $grup)
        <tr class="subsec"><td colspan="3">{{ $grup->nama_akun }}</td></tr>
        @foreach($grup->rincian as $row)
        <tr>
            <td class="indent">{{ $row->nama_akun }}</td>
            <td class="text-right">{{ pdfFmt($row->saldo) }}</td>
            <td class="text-right muted">@php $prGrup = collect($dataPrev['grupLiabilitas'] ?? [])->firstWhere('kode_akun', $grup->kode_akun); $pr = $prGrup ? collect($prGrup->rincian)->firstWhere('kode_akun', $row->kode_akun) : null; @endphp {{ $pr ? pdfFmt($pr->saldo) : '—' }}</td>
        </tr>
        @endforeach
        @endforeach
        @if(empty($data['grupLiabilitas']) || count($data['grupLiabilitas']) == 0)
        <tr><td class="indent muted italic" colspan="3">Tidak ada liabilitas</td></tr>
        @endif
        <tr class="total-green"><td>Jumlah Liabilitas</td><td class="text-right">{{ pdfFmt($data['jumlahLiabilitas'] ?? 0) }}</td><td class="text-right green2">{{ pdfFmt($dataPrev['jumlahLiabilitas'] ?? 0) }}</td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="sec"><td colspan="3">Aset Neto</td></tr>
        <tr class="subsec"><td colspan="3">Tanpa Pembatasan dari Pemberi Sumber Daya</td></tr>
        @foreach($data['rincianAsetNetoTanpa'] ?? [] as $row)
        <tr>
            <td class="indent">{{ $row->nama_akun }}</td>
            <td class="text-right">{{ pdfFmt($row->saldo) }}</td>
            <td class="text-right muted">@php $pr = collect($dataPrev['rincianAsetNetoTanpa'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp {{ $pr ? pdfFmt($pr->saldo) : '—' }}</td>
        </tr>
        @endforeach
        <tr class="subtotal"><td class="indent">Jumlah Aset Neto Tanpa Pembatasan</td><td class="text-right">{{ pdfFmt($data['asetNetoTanpaPembatasan'] ?? 0) }}</td><td class="text-right">{{ pdfFmt($dataPrev['asetNetoTanpaPembatasan'] ?? 0) }}</td></tr>
        <tr class="subsec"><td colspan="3">Dengan Pembatasan dari Pemberi Sumber Daya</td></tr>
        @foreach($data['rincianAsetNetoDengan'] ?? [] as $row)
        <tr>
            <td class="indent">{{ $row->nama_akun }}</td>
            <td class="text-right">{{ pdfFmt($row->saldo) }}</td>
            <td class="text-right muted">@php $pr = collect($dataPrev['rincianAsetNetoDengan'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp {{ $pr ? pdfFmt($pr->saldo) : '—' }}</td>
        </tr>
        @endforeach
        <tr class="subtotal"><td class="indent">Jumlah Aset Neto Dengan Pembatasan</td><td class="text-right">{{ pdfFmt($data['asetNetoDenganPembatasan'] ?? 0) }}</td><td class="text-right">{{ pdfFmt($dataPrev['asetNetoDenganPembatasan'] ?? 0) }}</td></tr>
        <tr class="total-green"><td>Jumlah Aset Neto</td><td class="text-right">{{ pdfFmt($data['jumlahAsetNeto'] ?? 0) }}</td><td class="text-right green2">{{ pdfFmt($dataPrev['jumlahAsetNeto'] ?? 0) }}</td></tr>
        @php $check = ($data['jumlahLiabilitas'] ?? 0) + ($data['jumlahAsetNeto'] ?? 0); $checkPrev = ($dataPrev['jumlahLiabilitas'] ?? 0) + ($dataPrev['jumlahAsetNeto'] ?? 0); @endphp
        <tr class="grand"><td>Jumlah Liabilitas dan Aset Neto</td><td class="text-right">{{ pdfFmt($check) }}</td><td class="text-right">{{ pdfFmt($checkPrev) }}</td></tr>
    </tbody>
</table>
@if(round($data['jumlahAset'] ?? 0) !== round($check))
<div class="warn-box">Perhatian: Total Aset (Rp {{ pdfFmt($data['jumlahAset'] ?? 0) }}) ≠ Liabilitas + Aset Neto (Rp {{ pdfFmt($check) }}). Periksa jurnal pembuka atau jurnal penutup.</div>
@endif
@endsection
