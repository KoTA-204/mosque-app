@extends('pages.laporan.pdf._layout')
@section('judul', 'Laporan Arus Kas')
@section('subjudul', 'Untuk Periode yang Berakhir ' . ($periode ? $periode->tanggal_akhir->translatedFormat('d F Y') : '—'))
@section('isi')
<table class="fin">
    <thead>
        <tr>
            <th class="text-left" style="width:55%;">Uraian</th>
            <th class="text-right">{{ $periode?->nama_periode ?? 'Periode Ini' }}</th>
            <th class="text-right">{{ $periodePrev?->nama_periode ?? 'Periode Lalu' }}</th>
        </tr>
    </thead>
    <tbody>
        <tr class="sec"><td colspan="3">Arus Kas dari Aktivitas Operasi</td></tr>
        <tr class="subsec"><td colspan="3">Penerimaan Kas</td></tr>
        @forelse($data['penerimaanOperasional'] ?? [] as $row)
        <tr><td class="indent">{{ $row->nama_akun }}</td><td class="text-right">{{ pdfFmt($row->saldo) }}</td><td class="text-right muted">—</td></tr>
        @empty
        <tr><td class="indent muted italic" colspan="3">Tidak ada penerimaan operasional</td></tr>
        @endforelse
        <tr class="subsec"><td colspan="3">Pengeluaran Kas</td></tr>
        @forelse($data['pengeluaranOperasional'] ?? [] as $row)
        <tr><td class="indent">{{ $row->nama_akun }}</td><td class="text-right red">({{ pdfFmt($row->saldo) }})</td><td class="text-right muted">—</td></tr>
        @empty
        <tr><td class="indent muted italic" colspan="3">Tidak ada pengeluaran operasional</td></tr>
        @endforelse
        <tr class="subtotal"><td>Kas Neto dari Aktivitas Operasi</td><td class="text-right">{{ pdfSigned($data['kasNetoOperasional'] ?? 0) }}</td><td class="text-right">{{ $dataPrev ? pdfSigned($dataPrev['kasNetoOperasional'] ?? 0) : '—' }}</td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="sec"><td colspan="3">Arus Kas dari Aktivitas Investasi</td></tr>
        @forelse($data['penerimaanInvestasi'] ?? [] as $row)
        <tr><td class="indent">{{ $row->nama_akun }}</td><td class="text-right">{{ pdfFmt($row->saldo) }}</td><td class="text-right muted">—</td></tr>
        @empty
        @endforelse
        @forelse($data['pengeluaranInvestasi'] ?? [] as $row)
        <tr><td class="indent">{{ $row->nama_akun }}</td><td class="text-right red">({{ pdfFmt($row->saldo) }})</td><td class="text-right muted">—</td></tr>
        @empty
        @endforelse
        @if((($data['penerimaanInvestasi'] ?? collect())->count() == 0) && (($data['pengeluaranInvestasi'] ?? collect())->count() == 0))
        <tr><td class="indent muted italic" colspan="3">Tidak ada aktivitas investasi</td></tr>
        @endif
        <tr class="subtotal"><td>Kas Neto untuk Aktivitas Investasi</td><td class="text-right">{{ pdfSigned($data['kasNetoInvestasi'] ?? 0) }}</td><td class="text-right">{{ $dataPrev ? pdfSigned($dataPrev['kasNetoInvestasi'] ?? 0) : '—' }}</td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="sec"><td colspan="3">Arus Kas dari Aktivitas Pendanaan</td></tr>
        @forelse($data['penerimaanPendanaan'] ?? [] as $row)
        <tr><td class="indent">{{ $row->nama_akun }}</td><td class="text-right">{{ pdfFmt($row->saldo) }}</td><td class="text-right muted">—</td></tr>
        @empty
        @endforelse
        @forelse($data['penyaluranPendanaan'] ?? [] as $row)
        <tr><td class="indent">{{ $row->nama_akun }}</td><td class="text-right red">({{ pdfFmt($row->saldo) }})</td><td class="text-right muted">—</td></tr>
        @empty
        @endforelse
        @if((($data['penerimaanPendanaan'] ?? collect())->count() == 0) && (($data['penyaluranPendanaan'] ?? collect())->count() == 0))
        <tr><td class="indent muted italic" colspan="3">Tidak ada aktivitas pendanaan</td></tr>
        @endif
        <tr class="subtotal"><td>Kas Neto dari Aktivitas Pendanaan</td><td class="text-right">{{ pdfSigned($data['kasNetoPendanaan'] ?? 0) }}</td><td class="text-right">{{ $dataPrev ? pdfSigned($dataPrev['kasNetoPendanaan'] ?? 0) : '—' }}</td></tr>
        <tr class="spacer"><td colspan="3"></td></tr>
        <tr class="grand"><td>Kenaikan (Penurunan) Neto Kas dan Setara Kas</td><td class="text-right">{{ pdfSigned($data['kenaikanNeto'] ?? 0) }}</td><td class="text-right">{{ $dataPrev ? pdfSigned($dataPrev['kenaikanNeto'] ?? 0) : '—' }}</td></tr>
        <tr><td>Kas dan Setara Kas pada Awal Periode</td><td class="text-right">{{ pdfFmt($data['kasAwal'] ?? 0) }}</td><td class="text-right muted">{{ $dataPrev ? pdfFmt($dataPrev['kasAwal'] ?? 0) : '—' }}</td></tr>
        <tr class="total-green"><td>Kas dan Setara Kas pada Akhir Periode</td><td class="text-right">{{ pdfFmt($data['kasAkhir'] ?? 0) }}</td><td class="text-right green2">{{ $dataPrev ? pdfFmt($dataPrev['kasAkhir'] ?? 0) : '—' }}</td></tr>
    </tbody>
</table>
@endsection
