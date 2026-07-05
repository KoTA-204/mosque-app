<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Periode;

/**
 * Kontrak semua laporan keuangan.
 *
 * Interface ini diletakkan satu folder (satu namespace) dengan implementasinya
 * (pola co-location), sehingga modul laporan bersifat self-contained.
 *
 * Setiap laporan (Posisi Keuangan, Penghasilan Komprehensif, Perubahan Aset
 * Neto, Arus Kas, dan CALK) menyusun datanya melalui method yang sama
 * (susunLaporan), sehingga controller dapat memperlakukannya secara polimorfik
 * tanpa perlu percabangan switch/if per jenis laporan.
 */
interface LaporanKeuanganInterface
{
    /**
     * Menyusun data laporan untuk satu periode.
     *
     * @param  Periode|null $periode            Periode yang sedang dilaporkan.
     * @param  Periode|null $periodeSebelumnya  Periode pembanding/sebelumnya.
     * @return array<string,mixed>
     */
    public function susunLaporan(?Periode $periode, ?Periode $periodeSebelumnya): array;

    /** Judul laporan untuk header halaman & nama file PDF. */
    public function judulLaporan(): string;

    /** Nama view Blade halaman, mis. "pages.laporan.posisi-keuangan". */
    public function namaViewHalaman(): string;

    /** Nama view Blade PDF, mis. "pages.laporan.pdf.posisi-keuangan". */
    public function namaViewPdf(): string;
}
