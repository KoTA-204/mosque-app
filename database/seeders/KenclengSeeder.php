<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kencleng;
use App\Models\Transaksi;
use App\Models\Kencleng;
use App\Models\Transaksi;

class KenclengSeeder extends Seeder
{
    public function run(): void
    {
        $transaksiKencleng = Transaksi::where('deskripsi', 'like', '%kencleng%')
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->orderBy('tanggal_transaksi')
            ->get();

        $nomorUrut = 1;
        foreach ($transaksiKencleng as $trx) {
            $nomor = str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
            Kencleng::create([
                'transaksi_id'   => $trx->id,
                'nomor_kwitansi' => 'BA-' . $nomor,
                // kolom NOT NULL — isi path placeholder; file PDF asli di-upload manual saat demo
                'berita_acara'   => 'berita-acara/ba-' . $nomor . '.pdf',
            ]);
            $nomorUrut++;
        }
    }
}
