<?php

/*
 |--------------------------------------------------------------------------
 | Helper format angka untuk template PDF laporan keuangan
 |--------------------------------------------------------------------------
 | File ini SENGAJA tidak memakai namespace, agar fungsi-fungsi di bawah
 | terdaftar di namespace global. View Blade yang sudah di-compile berjalan
 | di namespace global, sehingga bisa memanggil pdfFmt() dkk. secara langsung.
 | Di-load via require_once dari LaporanKeuanganController::downloadPdf().
 */

if (! function_exists('pdfFmt')) {
    function pdfFmt($v)
    {
        return number_format(abs((float) $v), 0, ',', '.');
    }
}

if (! function_exists('pdfSigned')) {
    function pdfSigned($v)
    {
        return ((float) $v) < 0 ? '(' . pdfFmt($v) . ')' : pdfFmt($v);
    }
}

if (! function_exists('pdfRp')) {
    function pdfRp($v)
    {
        return ((float) $v) < 0 ? '(Rp ' . pdfFmt($v) . ')' : 'Rp ' . pdfFmt($v);
    }
}

if (! function_exists('pdfPrev')) {
    function pdfPrev($dataPrev, $key)
    {
        if (! $dataPrev || ! isset($dataPrev[$key])) {
            return '—';
        }
        $v = $dataPrev[$key];
        return ((float) $v) == 0.0 ? '—' : pdfSigned($v);
    }
}
