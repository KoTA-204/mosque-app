@extends('pages.laporan.pdf._layout')
@section('judul', 'Laporan Penghasilan Komprehensif')
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
        <tr class="sec"><td colspan="3">Tanpa Pembatasan dari Pemberi Sumber Daya</td></tr>
        <tr class="subsec"><td colspan="3">Pendapatan</td></tr>
        @forelse($data['rincianTanpaPembatasan'] ?? [] as $row)
        <tr>
            <td class="indent">{{ $row->nama_akun }}</td>
            <td class="text-right">{{ pdfFmt($row->saldo) }}</td>
            <td class="text-right muted">@php $pr = collect($dataPrev['rincianTanpaPembatasan'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp {{ $pr ? pdfFmt($pr->saldo) : '—' }}</td>
        </tr>
        @empty
        <tr><td class="indent muted italic" colspan="3">Tidak ada pendapatan pada periode ini</td></tr>
        @endforelse
        <tr class="subtotal"><td class="indent">Total Pendapatan</td><td class="text-right">{{ pdfFmt($data['pendapatanTanpaPembatasan'] ?? 0) }}</td><td class="text-right">{{ pdfFmt($dataPrev['pendapatanTanpaPembatasan'] ?? 0) }}</td></tr>
        <tr class="subsec"><td colspan="3">Beban</td></tr>
        @foreach($data['grupBeban'] ?? [] as $grup)
        <tr><td class="indent italic muted" colspan="3">{{ $grup->nama_akun }}</td></tr>
        @foreach($grup->rincian as $row)
        <tr>
            <td class="indent2">{{ $row->nama_akun }}</td>
            <td class="text-right">{{ pdfFmt($row->saldo) }}</td>
            <td class="text-right muted">@php $prGrup = collect($dataPrev['grupBeban'] ?? [])->firstWhere('kode_akun', $grup->kode_akun); $pr = $prGrup ? collect($prGrup->rincian)->firstWhere('kode_akun', $row->kode_akun) : null; @endphp {{ $pr ? pdfFmt($pr->saldo) : '—' }}</td>
        </tr>
        @endforeach
        @endforeach
        @if(($data['jumlahBeban'] ?? 0) == 0)
        <tr><td class="indent muted italic" colspan="3">Tidak ada beban pada periode ini</td></tr>
        @endif
        <tr class="subtotal"><td class="indent">Total Beban</td><td class="text-right">({{ pdfFmt($data['jumlahBeban'] ?? 0) }})</td><td class="text-right">({{ pdfFmt($dataPrev['jumlahBeban'] ?? 0) }})</td></tr>
        <tr class="subtotal"><td>Surplus (Defisit) Tanpa Pembatasan</td><td class="text-right">{{ pdfSigned($data['surplusTanpaPembatasan'] ?? 0) }}</td><td class="text-right">{{ pdfSigned($dataPrev['surplusTanpaPembatasan'] ?? 0) }}</td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="sec"><td colspan="3">Dengan Pembatasan dari Pemberi Sumber Daya</td></tr>
        <tr class="subsec"><td colspan="3">Pendapatan</td></tr>
        @forelse($data['rincianDenganPembatasan'] ?? [] as $row)
        <tr>
            <td class="indent">{{ $row->nama_akun }}</td>
            <td class="text-right">{{ pdfFmt($row->saldo) }}</td>
            <td class="text-right muted">@php $pr = collect($dataPrev['rincianDenganPembatasan'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp {{ $pr ? pdfFmt($pr->saldo) : '—' }}</td>
        </tr>
        @empty
        <tr><td class="indent muted italic" colspan="3">Tidak ada pendapatan terikat pada periode ini</td></tr>
        @endforelse
        <tr class="subtotal"><td>Surplus (Defisit) Dengan Pembatasan</td><td class="text-right">{{ pdfSigned($data['surplusDenganPembatasan'] ?? 0) }}</td><td class="text-right">{{ pdfSigned($dataPrev['surplusDenganPembatasan'] ?? 0) }}</td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="total-green"><td>Surplus / (Defisit) Periode Berjalan</td><td class="text-right">{{ pdfSigned($data['surplusDefisit'] ?? 0) }}</td><td class="text-right green2">{{ pdfSigned($dataPrev['surplusDefisit'] ?? 0) }}</td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="total-green"><td>Total Penghasilan Komprehensif Periode Berjalan</td><td class="text-right">{{ pdfSigned($data['totalKomprehensif'] ?? 0) }}</td><td class="text-right green2">{{ pdfSigned($dataPrev['totalKomprehensif'] ?? 0) }}</td></tr>
    </tbody>
</table>
@endsection
