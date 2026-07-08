<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\DetailJurnal;
use App\Models\Periode;

class JurnalPembukaSeeder extends Seeder
{
    /**
     * Jurnal Pembuka - saldo awal per 1 Januari 2026.
     * Aturan: Debit = akun Aset; Kredit = Liabilitas + Aset Neto.
     *
     * Total Debit  = 2.165.000.000
     * Total Kredit = 2.165.000.000  (seimbang)
     */
    public function run(): void
    {
        // Saldo awal dipasang di periode bulan pertama (Januari 2026).
        $periode = Periode::where('nama_periode', 'Januari 2026')->first()
            ?? Periode::where('tipe', 'bulanan')->orderBy('tanggal_awal')->first();

        if (!$periode) {
            $this->command->warn('Periode tidak ditemukan. Jalankan PeriodeSeeder terlebih dahulu.');
            return;
        }

        $akun = Akun::pluck('id', 'kode_akun'); // 'kode_akun' => id

        $jurnal = Jurnal::create([
            'periode_id'       => $periode->id,
            'transaksi_id'     => null,
            'jurnal_ref_id'    => null,
            'tanggal'          => '2026-01-01',
            'tipe_penyesuaian' => null,
            'tipe_penutupan'   => null,
            'jenis_jurnal'     => 'PEMBUKA',
            'keterangan'       => 'Jurnal pembuka - saldo awal per 1 Januari 2026',
            'status'           => 'POSTED',
        ]);

        // [kode_akun, tipe, nominal]
        $detail = [
            // ===== DEBIT (Aset) =====
            ['1-1001', 'DEBIT', 5000000],      // Kas Kecil
            ['1-1002', 'DEBIT', 25000000],     // Kas Infak
            ['1-1003', 'DEBIT', 15000000],     // Kas Zakat
            ['1-1006', 'DEBIT', 10000000],     // Perlengkapan Masjid
            ['1-2001', 'DEBIT', 800000000],    // Tanah Masjid
            ['1-2002', 'DEBIT', 1200000000],   // Bangunan Masjid
            ['1-2005', 'DEBIT', 50000000],     // Investasi Jangka Panjang
            ['1-2006', 'DEBIT', 60000000],     // Peralatan Masjid
            // ===== KREDIT (kontra-aset, Liabilitas, Aset Neto) =====
            ['1-2003', 'KREDIT', 150000000],   // Akumulasi Penyusutan Bangunan
            ['1-2007', 'KREDIT', 20000000],    // Akumulasi Penyusutan Peralatan
            ['2-1001', 'KREDIT', 5000000],     // Utang Operasional
            ['3-2101', 'KREDIT', 15000000],    // Saldo Awal Aset Neto Dengan Pembatasan (dana zakat)
            ['3-1002', 'KREDIT', 1975000000],  // Saldo Awal Aset Neto (tanpa pembatasan)
        ];

        foreach ($detail as $d) {
            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id'   => $akun[$d[0]],
                'tipe'      => $d[1],
                'nominal'   => $d[2],
            ]);
        }
    }
}
