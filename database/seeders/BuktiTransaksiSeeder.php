<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BuktiTransaksi;
use App\Models\Transaksi;

class BuktiTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['deskripsi' => '%Infak Jumat minggu pertama Januari%', 'nama_file' => 'infak-jumat-jan.jpg',           'path_file' => 'bukti/infak-jumat-jan.jpg'],
            ['deskripsi' => '%Tagihan listrik Januari%',            'nama_file' => 'tagihan-listrik-jan.pdf',      'path_file' => 'bukti/tagihan-listrik-jan.pdf'],
            ['deskripsi' => '%Honor imam & marbot Januari%',        'nama_file' => 'kwitansi-honor-imam-jan.pdf',  'path_file' => 'bukti/kwitansi-honor-imam-jan.pdf'],
            ['deskripsi' => '%Pembelian sajadah%',                  'nama_file' => 'nota-sajadah.jpg',             'path_file' => 'bukti/nota-sajadah.jpg'],
        ];

        foreach ($items as $item) {
            $trx = Transaksi::where('deskripsi', 'like', $item['deskripsi'])->first();
            if ($trx) {
                BuktiTransaksi::create([
                    'transaksi_id' => $trx->id,
                    'nama_file'    => $item['nama_file'],
                    'path_file'    => $item['path_file'],
                ]);
            }
        }
    }
}
