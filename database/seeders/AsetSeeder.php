<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Aset;
use App\Models\Dompet;
use App\Models\KategoriTransaksi;
use App\Models\Transaksi;
use App\Models\User;

class AsetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dompet   = Dompet::first();
        $user     = User::first();
        $kategori = KategoriTransaksi::where('nama_kategori', 'like', '%aset%')
                        ->orWhere('nama_kategori', 'like', '%pembelian%')
                        ->first() ?? KategoriTransaksi::first();

        $trxSoundSystem  = Transaksi::where('deskripsi', 'Pembelian Sound System & Amplifier TOA')->first();
        $trxMobilJenazah = Transaksi::where('deskripsi', 'Pembelian Mobil Jenazah Toyota HiAce')->first();
        $trxAC           = Transaksi::where('deskripsi', 'Pembelian AC Split 2 PK (4 Unit)')->first();
        $trxKipas        = Transaksi::where('deskripsi', 'Pembelian Kipas Angin Gantung (6 Unit)')->first();
        $trxPompaAir     = Transaksi::where('deskripsi', 'Pembelian Mesin Pompa Air')->first();
        $trxGenerator    = Transaksi::where('deskripsi', 'Pembelian Generator Listrik 5000 Watt')->first();
        $trxJamSholat    = Transaksi::where('deskripsi', 'Pembelian Jam Digital Jadwal Sholat')->first();

        // ── Data Aset ──────────────────────────────────────────────
        $data = [

            // ── Tanah & Bangunan (Wakaf - tidak disusutkan) ────────
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'Tanah Wakaf Masjid',
                'sumber_perolehan'     => 'Wakaf',
                'tanggal_perolehan'    => '2000-01-10',
                'nilai_tercatat'       => 800000000,
                'umur_manfaat'         => null,   // tidak disusutkan
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Jl. Masjid Raya No. 1, Bandung',
                'nama_pemberi'         => 'H. Abdullah Bin Umar',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 800000000,
                'akumulasi_penyusutan' => null,   // tidak disusutkan
            ],

            // ── Bangunan (Wakaf - disusutkan) ──────────────────────
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'Bangunan Masjid Utama',
                'sumber_perolehan'     => 'Wakaf',
                'tanggal_perolehan'    => '2002-05-15',
                'nilai_tercatat'       => 1200000000,
                'umur_manfaat'         => 50,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Jl. Masjid Raya No. 1, Bandung',
                'nama_pemberi'         => 'Panitia Pembangunan Masjid',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 696000000,
                'akumulasi_penyusutan' => 504000000,
            ],
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'Gedung Madrasah / TPA',
                'sumber_perolehan'     => 'Infak Jamaah',
                'tanggal_perolehan'    => '2010-08-01',
                'nilai_tercatat'       => 350000000,
                'umur_manfaat'         => 30,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Jl. Masjid Raya No. 1, Bandung',
                'nama_pemberi'         => 'H. Rahmat Hidayat',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 210000000,
                'akumulasi_penyusutan' => 140000000,
            ],
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'Rumah Imam / Marbot',
                'sumber_perolehan'     => 'Wakaf',
                'tanggal_perolehan'    => '2012-03-20',
                'nilai_tercatat'       => 200000000,
                'umur_manfaat'         => 30,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Jl. Masjid Raya No. 1A, Bandung',
                'nama_pemberi'         => 'Hj. Fatimah Azzahra',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 136000000,
                'akumulasi_penyusutan' => 64000000,
            ],
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'Toilet & Tempat Wudhu',
                'sumber_perolehan'     => 'Infak Jamaah',
                'tanggal_perolehan'    => '2015-11-10',
                'nilai_tercatat'       => 80000000,
                'umur_manfaat'         => 20,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Samping Masjid Utama',
                'nama_pemberi'         => 'Panitia Renovasi Masjid',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 44000000,
                'akumulasi_penyusutan' => 36000000,
            ],

            // ── Peralatan Ibadah (Wakaf/Donasi - tidak disusutkan) ─
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'Mimbar Khotbah Kayu Jati',
                'sumber_perolehan'     => 'Wakaf',
                'tanggal_perolehan'    => '2005-04-10',
                'nilai_tercatat'       => 15000000,
                'umur_manfaat'         => null,   // tidak disusutkan
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Ruang Utama Masjid',
                'nama_pemberi'         => 'H. Syamsul Bachri',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 15000000,
                'akumulasi_penyusutan' => null,   // tidak disusutkan
            ],
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'Karpet Sajadah Masjid',
                'sumber_perolehan'     => 'Wakaf',
                'tanggal_perolehan'    => '2020-03-01',
                'nilai_tercatat'       => 25000000,
                'umur_manfaat'         => 5,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Ruang Utama Masjid',
                'nama_pemberi'         => 'Hj. Siti Maryam',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 5000000,
                'akumulasi_penyusutan' => 20000000,
            ],
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'Al-Quran (50 Eksemplar)',
                'sumber_perolehan'     => 'Donasi',
                'tanggal_perolehan'    => '2021-04-15',
                'nilai_tercatat'       => 5000000,
                'umur_manfaat'         => null,   // tidak disusutkan
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Rak Al-Quran Masjid',
                'nama_pemberi'         => 'Komunitas Peduli Quran',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 5000000,
                'akumulasi_penyusutan' => null,   // tidak disusutkan
            ],
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'Mukena (20 Set)',
                'sumber_perolehan'     => 'Donasi',
                'tanggal_perolehan'    => '2022-05-10',
                'nilai_tercatat'       => 3000000,
                'umur_manfaat'         => 3,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Ruang Wanita Masjid',
                'nama_pemberi'         => 'Ibu-Ibu Pengajian',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 1000000,
                'akumulasi_penyusutan' => 2000000,
            ],

            // ── Audio & Elektronik (Pembelian - ada transaksi) ─────
            [
                'transaksi_id'         => $trxSoundSystem->id,
                'nama_aset'            => 'Sound System & Amplifier TOA',
                'sumber_perolehan'     => 'Pembelian',
                'tanggal_perolehan'    => '2019-06-20',
                'nilai_tercatat'       => 18000000,
                'umur_manfaat'         => 8,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Ruang Utama Masjid',
                'nama_pemberi'         => '-',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 7000000,
                'akumulasi_penyusutan' => 10000000,
            ],
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'Menara Speaker Adzan (4 Unit)',
                'sumber_perolehan'     => 'Wakaf',
                'tanggal_perolehan'    => '2018-01-05',
                'nilai_tercatat'       => 12000000,
                'umur_manfaat'         => null,   // tidak disusutkan
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Menara Masjid',
                'nama_pemberi'         => 'H. Irfan Maulana',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 12000000,
                'akumulasi_penyusutan' => null,   // tidak disusutkan
            ],
            [
                'transaksi_id'         => $trxJamSholat->id,
                'nama_aset'            => 'Jam Digital Jadwal Sholat',
                'sumber_perolehan'     => 'Pembelian',
                'tanggal_perolehan'    => '2021-01-10',
                'nilai_tercatat'       => 3500000,
                'umur_manfaat'         => 5,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Ruang Utama Masjid',
                'nama_pemberi'         => '-',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 1750000,
                'akumulasi_penyusutan' => 1750000,
            ],
            [
                'transaksi_id'         => $trxAC->id,
                'nama_aset'            => 'AC Split 2 PK (4 Unit)',
                'sumber_perolehan'     => 'Pembelian',
                'tanggal_perolehan'    => '2020-07-15',
                'nilai_tercatat'       => 28000000,
                'umur_manfaat'         => 8,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Ruang Utama Masjid',
                'nama_pemberi'         => '-',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 14875000,
                'akumulasi_penyusutan' => 13125000,
            ],
            [
                'transaksi_id'         => $trxKipas->id,
                'nama_aset'            => 'Kipas Angin Gantung (6 Unit)',
                'sumber_perolehan'     => 'Pembelian',
                'tanggal_perolehan'    => '2017-09-01',
                'nilai_tercatat'       => 4800000,
                'umur_manfaat'         => 5,
                'kondisi_aset'         => 'RUSAK BERAT',
                'lokasi_aset'          => 'Gudang',
                'nama_pemberi'         => '-',
                'status_aset'          => 'TIDAK AKTIF',
                'nilai_buku'           => 0,
                'akumulasi_penyusutan' => 4800000,
            ],

            // ── Kendaraan (Pembelian - ada transaksi) ──────────────
            [
                'transaksi_id'         => $trxMobilJenazah->id,
                'nama_aset'            => 'Mobil Jenazah Toyota HiAce',
                'sumber_perolehan'     => 'Pembelian',
                'tanggal_perolehan'    => '2018-10-10',
                'nilai_tercatat'       => 280000000,
                'umur_manfaat'         => 10,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Garasi Masjid',
                'nama_pemberi'         => '-',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 126000000,
                'akumulasi_penyusutan' => 154000000,
            ],

            // ── Utilitas (Pembelian - ada transaksi) ───────────────
            [
                'transaksi_id'         => $trxPompaAir->id,
                'nama_aset'            => 'Mesin Pompa Air',
                'sumber_perolehan'     => 'Pembelian',
                'tanggal_perolehan'    => '2019-03-12',
                'nilai_tercatat'       => 5000000,
                'umur_manfaat'         => 8,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Ruang Utilitas',
                'nama_pemberi'         => '-',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 2406250,
                'akumulasi_penyusutan' => 3093750,
            ],
            [
                'transaksi_id'         => $trxGenerator->id,
                'nama_aset'            => 'Generator Listrik 5000 Watt',
                'sumber_perolehan'     => 'Pembelian',
                'tanggal_perolehan'    => '2020-12-01',
                'nilai_tercatat'       => 12000000,
                'umur_manfaat'         => 8,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Ruang Utilitas',
                'nama_pemberi'         => '-',
                'status_aset'          => 'AKTIF',
                'nilai_buku'           => 6000000,
                'akumulasi_penyusutan' => 5000000,
            ],

            // ── Draft ──────────────────────────────────────────────
            [
                'transaksi_id'         => null,
                'nama_aset'            => 'CCTV System (8 Kamera)',
                'sumber_perolehan'     => 'Infak Jamaah',
                'tanggal_perolehan'    => '2024-12-01',
                'nilai_tercatat'       => 8500000,
                'umur_manfaat'         => 5,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Belum dipasang',
                'nama_pemberi'         => 'Panitia Masjid',
                'status_aset'          => 'DRAFT',
                'nilai_buku'           => 8500000,
                'akumulasi_penyusutan' => 0,
            ],
        ];

        foreach ($data as $item) {
            Aset::create($item);
        }
    }
}
