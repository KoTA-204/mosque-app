@extends('pages.laporan.pdf._layout')
@section('judul', 'Catatan Atas Laporan Keuangan (CALK)')
@section('subjudul', 'Untuk Periode yang Berakhir ' . ($periode ? $periode->tanggal_akhir->translatedFormat('d F Y') : '—'))
@section('isi')
@php $tgl = $periode ? $periode->tanggal_akhir->translatedFormat('d F Y') : '—'; @endphp

<div class="catatan">
    <h3>1. Informasi Umum</h3>
    <p>Masjid Luqmanul Hakim merupakan entitas berorientasi nonlaba yang didirikan pada tanggal 10 Januari 2010 dan beralamat di Jl. Moilati No. 10, Kecamatan Ilir Barat I, Kota Palembang. Masjid ini bergerak dalam kegiatan pelayanan ibadah, dakwah, pendidikan, dan sosial kemasyarakatan.</p>
    @php
        $infoUmum = [
            'Pendirian'  => '10 Januari 2010',
            'Legalitas'  => 'Akta Pendirian No. 08 Tanggal 10 Januari 2010',
            'Ketua DKM'  => 'H. Abdul Latif, SE., MM',
            'Sekretaris' => 'M. Ridwan, S.Pd',
            'Bendahara'  => 'Narul Hidayah',
        ];
    @endphp
    <table class="info">
        @foreach($infoUmum as $label => $nilai)
        <tr><td>{{ $label }}</td><td>:</td><td>{{ $nilai }}</td></tr>
        @endforeach
    </table>
</div>

<div class="catatan">
    <h3>2. Dasar Penyusunan Laporan Keuangan</h3>
    <p>Laporan keuangan disusun sesuai dengan Interpretasi Standar Akuntansi Keuangan (ISAK) 35 tentang Penyajian Laporan Keuangan Entitas Berorientasi Nonlaba. Laporan keuangan disusun dengan basis akrual dan menggunakan mata uang Rupiah (IDR).</p>
</div>

<div class="catatan">
    <h3>3. Kas dan Setara Kas</h3>
    <p class="muted">Rincian kas dan setara kas per {{ $tgl }}:</p>
    <table class="fin">
        <thead><tr><th class="text-left" style="width:70%;">Uraian</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            @forelse($data['kasSetaraKas'] as $row)
            <tr><td>{{ $row->nama_akun }}</td><td class="text-right">Rp {{ pdfFmt($row->saldo) }}</td></tr>
            @empty
            <tr><td colspan="2" class="muted italic text-right">Tidak ada data kas</td></tr>
            @endforelse
            <tr class="subtotal"><td>Total Kas dan Setara Kas</td><td class="text-right">Rp {{ pdfFmt($data['totalKas']) }}</td></tr>
        </tbody>
    </table>
</div>

<div class="catatan">
    <h3>4. Piutang</h3>
    <p class="muted">Rincian piutang per {{ $tgl }}:</p>
    <table class="fin">
        <thead><tr><th class="text-left" style="width:70%;">Uraian</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            @forelse($data['piutang'] as $row)
            <tr><td>{{ $row->nama_akun }}</td><td class="text-right">Rp {{ pdfFmt($row->saldo) }}</td></tr>
            @empty
            <tr><td colspan="2" class="muted italic text-right">Tidak ada piutang</td></tr>
            @endforelse
            <tr class="subtotal"><td>Total Piutang</td><td class="text-right">Rp {{ pdfFmt($data['totalPiutang']) }}</td></tr>
        </tbody>
    </table>
</div>

<div class="catatan">
    <h3>5. Aset Tetap</h3>
    <p class="muted">Rincian aset tetap per {{ $tgl }}:</p>
    <table class="fin">
        <thead><tr>
            <th class="text-left">Uraian</th>
            <th class="text-right">Harga Perolehan</th>
            <th class="text-right">Akm. Penyusutan</th>
            <th class="text-right">Nilai Buku</th>
        </tr></thead>
        <tbody>
            @forelse($data['asetTetap'] as $row)
            <tr>
                <td class="{{ $row->is_akumulasi ? 'indent muted italic' : '' }}">{{ $row->nama_akun }}</td>
                <td class="text-right">{{ $row->harga_perolehan != 0 ? 'Rp ' . pdfFmt($row->harga_perolehan) : '–' }}</td>
                <td class="text-right red">{{ $row->akumulasi != 0 ? '(Rp ' . pdfFmt($row->akumulasi) . ')' : '–' }}</td>
                <td class="text-right {{ $row->nilai_buku < 0 ? 'red' : '' }}">{{ $row->is_akumulasi ? '(Rp ' . pdfFmt(abs($row->nilai_buku)) . ')' : 'Rp ' . pdfFmt(abs($row->nilai_buku)) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="muted italic text-right">Tidak ada aset tetap</td></tr>
            @endforelse
            <tr class="subtotal">
                <td>Total Aset Tetap</td>
                <td class="text-right">Rp {{ pdfFmt($data['totalHargaPerolehan']) }}</td>
                <td class="text-right red">(Rp {{ pdfFmt($data['totalAkumulasi']) }})</td>
                <td class="text-right">Rp {{ pdfFmt($data['totalNilaiBuku']) }}</td>
            </tr>
        </tbody>
    </table>
    <p class="muted italic" style="font-size:8.5pt; margin-top:4pt;">Metode penyusutan menggunakan garis lurus dengan estimasi umur manfaat sesuai kebijakan entitas.</p>
</div>

<div class="catatan">
    <h3>6. Liabilitas</h3>
    <p class="muted">Rincian liabilitas per {{ $tgl }}:</p>
    <table class="fin">
        <thead><tr><th class="text-left" style="width:70%;">Uraian</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            @forelse($data['liabilitas'] as $row)
            <tr><td>{{ $row->nama_akun }}</td><td class="text-right">Rp {{ pdfFmt($row->saldo) }}</td></tr>
            @empty
            <tr><td colspan="2" class="muted italic text-right">Tidak ada liabilitas</td></tr>
            @endforelse
            <tr class="subtotal"><td>Total Liabilitas</td><td class="text-right">Rp {{ pdfFmt($data['totalLiabilitas']) }}</td></tr>
        </tbody>
    </table>
</div>

<div class="catatan">
    <h3>7. Pendapatan Infak dan Sedekah</h3>
    <p class="muted">Rincian pendapatan infak dan sedekah untuk periode yang berakhir {{ $tgl }}:</p>
    <table class="fin">
        <thead><tr><th class="text-left" style="width:70%;">Uraian</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            @forelse($data['pendapatanTanpa'] as $row)
            <tr><td>{{ $row->nama_akun }}</td><td class="text-right">Rp {{ pdfFmt($row->saldo) }}</td></tr>
            @empty
            <tr><td colspan="2" class="muted italic text-right">Tidak ada pendapatan pada periode ini</td></tr>
            @endforelse
            <tr class="subtotal"><td>Total Pendapatan Infak dan Sedekah</td><td class="text-right">Rp {{ pdfFmt($data['totalPendapatanTanpa']) }}</td></tr>
        </tbody>
    </table>
</div>

<div class="catatan">
    <h3>8. Beban Operasional</h3>
    <p class="muted">Rincian beban operasional untuk periode yang berakhir {{ $tgl }}:</p>
    <table class="fin">
        <thead><tr><th class="text-left" style="width:70%;">Uraian</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            @forelse($data['beban'] as $row)
            <tr><td>{{ $row->nama_akun }}</td><td class="text-right red">(Rp {{ pdfFmt($row->saldo) }})</td></tr>
            @empty
            <tr><td colspan="2" class="muted italic text-right">Tidak ada beban pada periode ini</td></tr>
            @endforelse
            <tr class="subtotal"><td>Total Beban Operasional</td><td class="text-right red">(Rp {{ pdfFmt($data['totalBeban']) }})</td></tr>
        </tbody>
    </table>
</div>

@php $an = $data['asetNeto']; @endphp
<div class="catatan">
    <h3>9. Aset Neto</h3>
    <p class="muted">Rincian aset neto per {{ $tgl }}:</p>
    <table class="fin">
        <thead><tr>
            <th class="text-left">Uraian</th>
            <th class="text-right">Tanpa Pembatasan</th>
            <th class="text-right">Dengan Pembatasan</th>
            <th class="text-right">Total</th>
        </tr></thead>
        <tbody>
            <tr>
                <td>Aset Neto Awal</td>
                <td class="text-right">Rp {{ pdfFmt($an['saldoAwalTanpa'] ?? 0) }}</td>
                <td class="text-right">Rp {{ pdfFmt($an['saldoAwalDengan'] ?? 0) }}</td>
                <td class="text-right">Rp {{ pdfFmt($an['totalSaldoAwal'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>Surplus (Defisit) Periode Ini</td>
                <td class="text-right">{{ pdfRp($an['surplusTanpa'] ?? 0) }}</td>
                <td class="text-right">{{ pdfRp($an['surplusDengan'] ?? 0) }}</td>
                <td class="text-right">{{ pdfRp(($an['surplusTanpa'] ?? 0) + ($an['surplusDengan'] ?? 0)) }}</td>
            </tr>
            <tr class="muted">
                <td class="italic">Reklasifikasi</td>
                <td class="text-right">Rp 0</td>
                <td class="text-right">Rp 0</td>
                <td class="text-right">Rp 0</td>
            </tr>
            <tr class="subtotal">
                <td>Saldo Aset Neto Akhir</td>
                <td class="text-right">Rp {{ pdfFmt($an['saldoAkhirTanpa'] ?? 0) }}</td>
                <td class="text-right">Rp {{ pdfFmt($an['saldoAkhirDengan'] ?? 0) }}</td>
                <td class="text-right">Rp {{ pdfFmt($an['totalSaldoAkhir'] ?? 0) }}</td>
            </tr>
        </tbody>
    </table>
</div>

@php $ak = $data['arusKas']; @endphp
<div class="catatan">
    <h3>10. Arus Kas</h3>
    <p class="muted">Informasi arus kas untuk periode yang berakhir {{ $tgl }}:</p>
    <table class="fin">
        <thead><tr><th class="text-left" style="width:70%;">Uraian</th><th class="text-right">Jumlah</th></tr></thead>
        <tbody>
            <tr><td>Kas Neto dari Aktivitas Operasional</td><td class="text-right">{{ pdfRp($ak['operasional'] ?? 0) }}</td></tr>
            <tr><td>Kas Neto dari Aktivitas Investasi</td><td class="text-right">{{ pdfRp($ak['investasi'] ?? 0) }}</td></tr>
            <tr><td>Kas Neto dari Aktivitas Pendanaan</td><td class="text-right">Rp 0</td></tr>
            <tr class="subtotal"><td>Kenaikan (Penurunan) Kas dan Setara Kas</td><td class="text-right">{{ pdfRp($ak['kenaikan'] ?? 0) }}</td></tr>
            <tr><td>Kas dan Setara Kas Awal</td><td class="text-right">Rp {{ pdfFmt($ak['kasAwal'] ?? 0) }}</td></tr>
            <tr class="subtotal"><td>Kas dan Setara Kas Akhir</td><td class="text-right">Rp {{ pdfFmt($ak['kasAkhir'] ?? 0) }}</td></tr>
        </tbody>
    </table>
</div>

<div class="catatan">
    <h3>11. Peristiwa Setelah Tanggal Pelaporan</h3>
    <p>Tidak terdapat peristiwa penting setelah tanggal {{ $tgl }} yang mempengaruhi laporan keuangan.</p>
</div>
@endsection
