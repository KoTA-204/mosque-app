<?php

namespace App\Services\Akuntansi;

use App\Models\Periode;

class PeriodeService
{
    public function isPeriodeClosed(Periode $periode): bool
    {
        return !$periode->status;
    }

    public function tutupPeriode(Periode $periode): void
    {
        $periode->update(['status' => false]);
    }

    public function getNextPeriode(Periode $periode): ?Periode
    {
        return Periode::where('tanggal_awal', '>', $periode->tanggal_awal)
            ->orderBy('tanggal_awal')
            ->first();
    }

    public function aktifkanPeriodeBerikutnya(Periode $periode): void
    {
        $next = $this->getNextPeriode($periode) ?? $this->buatPeriodeBerikutnya($periode);

        Periode::query()->update(['status' => false]);
        $next->update(['status' => true]);
    }

    public function buatPeriodeBerikutnya(Periode $periode): Periode
    {
        $awal  = $periode->tanggal_akhir->copy()->addDay()->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();

        return Periode::firstOrCreate(
            [
                'tanggal_awal'  => $awal->toDateString(),
                'tanggal_akhir' => $akhir->toDateString(),
            ],
            [
                'nama_periode' => $awal->translatedFormat('F Y'),
                'tipe'         => 'bulanan',
                'status'       => false,
            ]
        );
    }

    public function finalisasiPenutupan(Periode $periode): void
    {
        $this->tutupPeriode($periode);
        $this->aktifkanPeriodeBerikutnya($periode);
    }
}
