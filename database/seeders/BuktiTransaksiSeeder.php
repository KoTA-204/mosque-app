<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BuktiTransaksi;
use App\Models\Transaksi;

class BuktiTransaksiSeeder extends Seeder
{
    /**
     * Lampiran bukti untuk sebagian transaksi (dummy path).
     */
    public function run(): void
    {
        $data = [
            ['no_referensi' => 'INF-KCL-2026-03-001', 'nama_file' => 'berita-acara-kencleng-2026-03-13.pdf', 'path_file' => 'bukti/kencleng/berita-acara-kencleng-2026-03-13.pdf'],
            ['no_referensi' => 'OPS-2026-06-001',     'nama_file' => 'struk-pln-mei-2026.jpg',             'path_file' => 'bukti/operasional/struk-pln-mei-2026.jpg'],
            ['no_referensi' => 'OPS-2026-04-001',     'nama_file' => 'nota-kebersihan-2026-04.jpg',         'path_file' => 'bukti/operasional/nota-kebersihan-2026-04.jpg'],
            ['no_referensi' => 'QRB-2026-06-001',     'nama_file' => 'nota-pembelian-hewan-qurban.pdf',     'path_file' => 'bukti/qurban/nota-pembelian-hewan-qurban.pdf'],
            ['no_referensi' => 'SOS-2026-03-001',     'nama_file' => 'dokumentasi-baksos-ramadhan.pdf',     'path_file' => 'bukti/sosial/dokumentasi-baksos-ramadhan.pdf'],
            ['no_referensi' => 'ZKT-2026-06-001',     'nama_file' => 'bukti-transfer-zakat-maal.jpg',       'path_file' => 'bukti/zakat/bukti-transfer-zakat-maal.jpg'],
        ];

        foreach ($data as $row) {
            $transaksi = Transaksi::where('no_referensi', $row['no_referensi'])->first();
            if (! $transaksi) {
                continue;
            }

            BuktiTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'nama_file'    => $row['nama_file'],
                'path_file'    => $row['path_file'],
            ]);
        }
    }
}
