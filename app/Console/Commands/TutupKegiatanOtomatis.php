<?php

namespace App\Console\Commands;

use App\Models\Kegiatan;
use Illuminate\Console\Command;

class TutupKegiatanOtomatis extends Command
{
    protected $signature   = 'kegiatan:tutup-otomatis';
    protected $description = 'Menutup otomatis kegiatan yang tanggalnya sudah selesai dan semua transaksinya sudah APPROVED.';

    public function handle(): int
    {
        // Ambil semua kegiatan yang masih AKTIF dan tanggal selesainya sudah lewat
        $kandidat = Kegiatan::where('status', Kegiatan::STATUS_AKTIF)
            ->where(function ($q) {
                $today = now()->startOfDay();
                $q->where('tanggal_selesai', '<', $today)
                  ->orWhere(function ($q2) use ($today) {
                      // Jika tidak ada tanggal_selesai, gunakan tanggal_mulai
                      $q2->whereNull('tanggal_selesai')
                         ->where('tanggal_mulai', '<', $today);
                  });
            })
            ->get();

        $ditutup = 0;

        foreach ($kandidat as $kegiatan) {
            // Harus ada minimal 1 transaksi
            if (! $kegiatan->transaksi()->exists()) continue;

            // Di TutupKegiatanOtomatis
            $adaBelumApproved = $kegiatan->transaksi()
                ->whereNotNull('status_persetujuan')
                ->whereNotIn('status_persetujuan', ['APPROVED'])
                ->exists();

            if ($adaBelumApproved) continue;

            $kegiatan->update(['status' => Kegiatan::STATUS_DITUTUP]);
            $ditutup++;

            $this->line("Ditutup: [{$kegiatan->id}] {$kegiatan->nama_kegiatan}");
        }

        $this->info("Selesai. Total kegiatan ditutup: {$ditutup}");

        return Command::SUCCESS;
    }
}