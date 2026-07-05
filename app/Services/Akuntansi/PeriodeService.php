<?php

namespace App\Services\Akuntansi;

use App\Models\Periode;

/**
 * Service khusus siklus hidup & transisi periode akuntansi.
 *
 * Dipisahkan dari JurnalPenutupService demi High Cohesion:
 * class ini HANYA mengurus status dan transisi periode.
 */
class PeriodeService
{
    /** Periode dianggap CLOSED jika tidak aktif. */
    public function isPeriodeClosed(Periode $periode): bool
    {
        return !$periode->status;
    }

    /** Tutup periode saat ini. */
    public function tutupPeriode(Periode $periode): void
    {
        $periode->update(['status' => false]);
    }

    /** Ambil periode berikutnya berdasarkan tanggal awal. */
    public function getNextPeriode(Periode $periode): ?Periode
    {
        return Periode::where('tanggal_awal', '>', $periode->tanggal_awal)
            ->orderBy('tanggal_awal')
            ->first();
    }

    /** Aktifkan periode berikutnya (menonaktifkan semua periode lain). */
    public function aktifkanPeriodeBerikutnya(Periode $periode): void
    {
        $next = $this->getNextPeriode($periode);

        if (!$next) {
            throw new \RuntimeException('Periode berikutnya belum tersedia.');
        }

        Periode::query()->update(['status' => false]);
        $next->update(['status' => true]);
    }

    /**
     * Finalisasi penutupan:
     * - tutup periode sekarang
     * - aktifkan periode berikutnya
     */
    public function finalisasiPenutupan(Periode $periode): void
    {
        $this->tutupPeriode($periode);
        $this->aktifkanPeriodeBerikutnya($periode);
    }

    /** Guard: periode berikutnya harus sudah ada sebelum closing. */
    public function validasiPeriodeBerikutnya(Periode $periode): ?string
    {
        if (!$this->getNextPeriode($periode)) {
            return 'Periode berikutnya belum tersedia. '
                 . 'Buat periode berikutnya terlebih dahulu sebelum menutup periode ini.';
        }

        return null;
    }
}
