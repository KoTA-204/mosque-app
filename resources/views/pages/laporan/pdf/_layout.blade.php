<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>{{ $namaFile ?? 'Laporan' }}</title>
<style>
    @page { margin: 1.6cm 1.8cm 1.8cm 1.8cm; }
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #111; line-height: 1.45; margin: 0; padding: 0; }
    .kop { text-align: center; margin-bottom: 6pt; }
    .kop .masjid { font-size: 9pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.12em; color: #15803d; margin: 0 0 2pt; }
    .kop h2 { font-size: 15pt; font-weight: bold; color: #111; margin: 2pt 0; }
    .kop .periode { font-size: 10pt; color: #6b7280; margin: 2pt 0; }
    .kop .badge { display: inline-block; margin-top: 5pt; font-size: 8pt; color: #15803d; background: #dcfce7; padding: 2pt 12pt; border-radius: 20pt; }
    .kop-divider { border: none; border-top: 1.5pt solid #16a34a; margin: 10pt 0 14pt; }
    table.fin { width: 100%; border-collapse: collapse; font-size: 10pt; }
    table.fin th { background: #f3f4f6; font-size: 8.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.03em; padding: 5pt 8pt; border: 0.5pt solid #9ca3af; color: #374151; }
    table.fin td { padding: 4pt 8pt; border: 0.5pt solid #d1d5db; color: #111; vertical-align: top; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    tr.sec td { background: #eef2f7; font-weight: bold; text-transform: uppercase; font-size: 8.5pt; letter-spacing: 0.04em; color: #374151; }
    tr.subsec td { font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #6b7280; }
    .indent { padding-left: 20pt !important; }
    .indent2 { padding-left: 32pt !important; }
    tr.total-green td { background: #15803d; color: #fff; font-weight: bold; text-transform: uppercase; font-size: 9.5pt; letter-spacing: 0.02em; }
    tr.total-green td.green2 { color: #bbf7d0; }
    tr.subtotal td { font-weight: bold; background: #f0fdf4; }
    tr.grand td { font-weight: bold; border-top: 1.5pt solid #15803d; }
    .red { color: #dc2626; }
    .muted { color: #6b7280; }
    .italic { font-style: italic; }
    tr.spacer td { border: none !important; padding: 3pt 0 !important; background: #fff !important; }
    .note-box { margin-top: 14pt; padding: 8pt 10pt; background: #f9fafb; border: 0.5pt solid #e5e7eb; font-size: 8.5pt; color: #6b7280; }
    .note-box p { margin: 0 0 4pt; }
    .warn-box { margin-top: 12pt; padding: 8pt 10pt; background: #fef2f2; border: 0.5pt solid #fecaca; font-size: 9pt; color: #b91c1c; border-radius: 4pt; }
    table.info { border: none; font-size: 10pt; width: auto; }
    table.info td { border: none !important; padding: 2pt 8pt 2pt 0 !important; color: #333; vertical-align: top; }
    table.info td:first-child { color: #555; width: 110pt; }
    table.info td:nth-child(2) { width: 10pt; color: #555; }
    .catatan { margin-bottom: 14pt; page-break-inside: avoid; }
    .catatan h3 { font-size: 11pt; font-weight: bold; color: #15803d; margin: 0 0 5pt; padding-bottom: 2pt; border-bottom: 0.5pt solid #d1fae5; }
    .catatan p { font-size: 10pt; color: #333; margin: 0 0 6pt; line-height: 1.55; }
    @php
        if (!function_exists('pdfFmt')) { function pdfFmt($v) { return number_format(abs((float)$v), 0, ',', '.'); } }
        if (!function_exists('pdfSigned')) { function pdfSigned($v) { $v = (float)$v; return $v < 0 ? '(' . pdfFmt($v) . ')' : pdfFmt($v); } }
        if (!function_exists('pdfRp')) { function pdfRp($v) { $v = (float)$v; return $v < 0 ? '(Rp ' . pdfFmt($v) . ')' : 'Rp ' . pdfFmt($v); } }
        if (!function_exists('pdfPrev')) { function pdfPrev($dataPrev, $key) { if (!$dataPrev) return '—'; $v = $dataPrev[$key] ?? 0; return $v == 0 ? '—' : pdfSigned($v); } }
    @endphp
</style>
</head>
<body>
    <div class="kop">
        <p class="masjid">Masjid Luqmanul Hakim</p>
        <h2>@yield('judul')</h2>
        <p class="periode">@yield('subjudul')</p>
        <span class="badge">ISAK 335 — Penyajian Laporan Keuangan Entitas Berorientasi Nonlaba</span>
    </div>
    <hr class="kop-divider">
    @yield('isi')
</body>
</html>
