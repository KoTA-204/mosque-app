<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SyncKotaJadwalShalat extends Command
{
    protected $signature = 'jadwal-shalat:sync-kota';
    protected $description = 'Sinkronisasi daftar provinsi & kabupaten/kota dari equran.id ke cache';

    public function handle()
    {
        $this->info('Mengambil daftar provinsi...');

        $provinsiRes = Http::timeout(15)->get('https://equran.id/api/v2/shalat/provinsi');

        if (!$provinsiRes->successful()) {
            $this->error('Gagal mengambil daftar provinsi.');
            return self::FAILURE;
        }

        $provinsiList = $provinsiRes->json()['data'] ?? [];
        $result = [];

        $bar = $this->output->createProgressBar(count($provinsiList));
        $bar->start();

        foreach ($provinsiList as $provinsi) {
            try {
                $kabkotaRes = Http::timeout(15)->post('https://equran.id/api/v2/shalat/kabkota', [
                    'provinsi' => $provinsi,
                ]);

                foreach ($kabkotaRes->json()['data'] ?? [] as $kabkota) {
                    $result[] = [
                        'provinsi' => $provinsi,
                        'kabkota'  => $kabkota,
                        'label'    => "{$kabkota}, {$provinsi}",
                    ];
                }
            } catch (\Exception $e) {
                $this->warn("Gagal ambil kabkota untuk provinsi: {$provinsi}");
            }

            $bar->advance();
            usleep(200000); // jeda 0.2 detik antar request, sopan ke server equran.id
        }

        $bar->finish();
        $this->newLine();

        Cache::put('daftar_provinsi_kabkota', $result, now()->addDays(30));

        $this->info('Selesai. Total kota tersimpan: ' . count($result));
        return self::SUCCESS;
    }
}