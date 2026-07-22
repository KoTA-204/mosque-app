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

    public function aktifkanPeriodeBerikutnya(Periode $periode): Periode
    {
        // Multi-periode: JANGAN menutup periode lain. Cukup pastikan periode
        // berikutnya ada dan terbuka. Periode lain yang masih open dibiarkan
        // apa adanya, sehingga beberapa periode bisa terbuka bersamaan.
        $next = $this->getNextPeriode($periode) ?? $this->buatPeriodeBerikutnya($periode);

        if (! $next->status) {
            $next->update(['status' => true]);
        }

        return $next;
    }

    /**
     * Buka (buat bila perlu) periode setelah periode terbaru yang ada.
     * Dipakai tombol manual "Buka periode berikutnya" agar bendahara bisa
     * mencatat transaksi bulan baru sebelum periode berjalan ditutup.
     */
    public function bukaPeriodeBerikutnya(): Periode
    {
        $terbaru = Periode::orderByDesc('tanggal_awal')->first();

        if (! $terbaru) {
            throw new \RuntimeException('Belum ada periode. Buat jurnal pembuka terlebih dahulu.');
        }

        // Periode berikutnya hanya boleh dibuka setelah periode berjalan mencapai
        // tanggal akhirnya. Contoh: periode Januari (akhir 31 Jan) baru bisa
        // membuka periode berikutnya mulai 31 Januari dan seterusnya.
        $hariIni      = now()->startOfDay();
        $akhirPeriode = $terbaru->tanggal_akhir->copy()->startOfDay();
        if ($hariIni->lt($akhirPeriode)) {
            throw new \RuntimeException(
                'Periode berikutnya baru dapat dibuka mulai '
                . $akhirPeriode->translatedFormat('d F Y')
                . ' (akhir periode ' . $terbaru->nama_periode . '). '
                . 'Hari ini baru ' . $hariIni->translatedFormat('d F Y') . '.'
            );
        }

        return $this->aktifkanPeriodeBerikutnya($terbaru);
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
