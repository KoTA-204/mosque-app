<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - {{ $periodeAktif?->nama_periode ?? 'Masjid Luqmanul Hakim' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Helvetica Neue", Arial, sans-serif; color: #1f2937; padding: 32px; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #16a34a; padding-bottom: 14px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; color: #166534; }
        .header h2 { font-size: 14px; font-weight: 600; margin-top: 2px; }
        .header p { font-size: 11px; color: #6b7280; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 7px 9px; text-align: left; }
        th { background: #f0fdf4; color: #166534; font-size: 11px; text-transform: uppercase; letter-spacing: .03em; }
        td.right, th.right { text-align: right; white-space: nowrap; }
        td.center { text-align: center; color: #9ca3af; padding: 24px; }
        tr:nth-child(even) td { background: #fafafa; }
        .pemasukan { color: #16a34a; font-weight: 600; }
        .pengeluaran { color: #dc2626; font-weight: 600; }
        .toolbar { text-align: center; margin-bottom: 20px; }
        .toolbar button { background: #16a34a; color: #fff; border: none; padding: 9px 20px; border-radius: 8px; font-size: 13px; cursor: pointer; }
        .footer { margin-top: 24px; text-align: right; font-size: 10px; color: #9ca3af; }
        @media print { .toolbar { display: none; } body { padding: 0; } }
    </style>
</head>
<body onload="window.print()">
    <div class="toolbar">
        <button onclick="window.print()">Cetak / Simpan sebagai PDF</button>
    </div>
    <div class="header">
        <h1>Masjid Luqmanul Hakim</h1>
        <h2>Laporan Transaksi Keuangan</h2>
        <p>Periode: {{ $periodeAktif?->nama_periode ?? '-' }}</p>
        <p>Dicetak pada {{ $now->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Kategori</th>
                <th class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
        @forelse($transaksi as $i => $t)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ optional($t->tanggal_transaksi)->translatedFormat('d M Y') }}</td>
                <td>{{ ucfirst(strtolower($t->jenis_transaksi)) }}</td>
                <td>{{ $t->deskripsi ?? '-' }}</td>
                <td>{{ $t->kategoriTransaksi?->nama_kategori ?? '-' }}</td>
                <td class="right {{ $t->jenis_transaksi === 'PEMASUKAN' ? 'pemasukan' : 'pengeluaran' }}">
                    {{ $t->jenis_transaksi === 'PEMASUKAN' ? '+' : '-' }}Rp {{ number_format($t->jumlah, 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="center">Belum ada transaksi pada periode ini.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="footer">Dokumen dibuat otomatis oleh Sistem Keuangan Masjid Luqmanul Hakim</div>
</body>
</html>