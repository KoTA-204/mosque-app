<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaksi;
use App\Models\Dompet;
use App\Models\Kegiatan;
use App\Models\KategoriTransaksi;
use App\Models\User;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $user          = User::first();
        $kasKotak      = Dompet::where('nama_dompet', 'Kas Masjid')->first();
        $rekOperasional= Dompet::where('nama_dompet', 'Bank BSI Operasional')->first();

        $katInfakJumat  = KategoriTransaksi::where('nama_kategori', 'Infak Jumat')->first();
        $katInfakHarian = KategoriTransaksi::where('nama_kategori', 'Infak Harian')->first();
        $katKencleng    = KategoriTransaksi::where('nama_kategori', 'Kencleng')->first();
        $katOperasional = KategoriTransaksi::where('nama_kategori', 'Operasional Masjid')->first();
        $katAset        = KategoriTransaksi::where('nama_kategori', 'Pembelian Aset')->first();
        $katRenovasi    = KategoriTransaksi::where('nama_kategori', 'Perawatan & Renovasi')->first();
        $katHonor       = KategoriTransaksi::where('nama_kategori', 'Honorarium')->first();
        $katPerlengkapan= KategoriTransaksi::where('nama_kategori', 'Perlengkapan Ibadah')->first();
        $katSosial      = KategoriTransaksi::where('nama_kategori', 'Sosial & Santunan')->first();
        $katKegiatan    = KategoriTransaksi::where('nama_kategori', 'Kegiatan')->first();
        $katDonasi      = KategoriTransaksi::where('nama_kategori', 'Donasi Kegiatan')->first();

        $kegQurban = Kegiatan::where('nama_kegiatan', 'Qurban 1447 H')->first();
        $kegZakat  = Kegiatan::where('nama_kegiatan', 'Zakat Fitrah 1447 H')->first();
        $kegKajian = Kegiatan::where('nama_kegiatan', 'Kajian Ramadan 1447 H')->first();
        $kegSosial = Kegiatan::where('nama_kegiatan', 'Bakti Sosial Idul Adha 1447 H')->first();

        // ══════════════════════════════════════════════════════
        // TRANSAKSI DICATAT BENDAHARA LANGSUNG
        // status_approval = null, status_jurnal = MAPPED
        // ══════════════════════════════════════════════════════
        $transaksiiBendahara = [

            // Operasional rutin
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-01-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 850000,   'deskripsi' => 'Tagihan listrik Januari'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-01-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 250000,   'deskripsi' => 'Tagihan air PDAM Januari'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-02-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 900000,   'deskripsi' => 'Tagihan listrik Februari'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-02-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 230000,   'deskripsi' => 'Tagihan air PDAM Februari'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-03-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1100000,  'deskripsi' => 'Tagihan listrik Maret (Ramadhan)'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-03-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 260000,   'deskripsi' => 'Tagihan air PDAM Maret'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-04-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 870000,   'deskripsi' => 'Tagihan listrik April'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-05-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 820000,   'deskripsi' => 'Tagihan listrik Mei'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-01-15', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 300000,   'deskripsi' => 'Pembelian alat kebersihan masjid'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-02-15', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 275000,   'deskripsi' => 'Pembelian sabun dan pewangi masjid'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-03-20', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 180000,   'deskripsi' => 'Pembelian perlengkapan kebersihan Ramadhan'],

            // Honorarium
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-01-31', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2000000, 'deskripsi' => 'Honor imam & marbot Januari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-02-29', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2000000, 'deskripsi' => 'Honor imam & marbot Februari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-03-31', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 3000000, 'deskripsi' => 'Honor imam tarawih & marbot Ramadhan'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-04-30', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2000000, 'deskripsi' => 'Honor imam & marbot April'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-05-31', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2000000, 'deskripsi' => 'Honor imam & marbot Mei'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-01-07', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,  'deskripsi' => 'Honor khatib Jumat Januari minggu 1'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-01-14', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,  'deskripsi' => 'Honor khatib Jumat Januari minggu 2'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-01-21', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,  'deskripsi' => 'Honor khatib Jumat Januari minggu 3'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-01-28', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,  'deskripsi' => 'Honor khatib Jumat Januari minggu 4'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-02-04', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,  'deskripsi' => 'Honor khatib Jumat Februari minggu 1'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katHonor->id, 'tanggal_transaksi' => '2024-02-11', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,  'deskripsi' => 'Honor khatib Jumat Februari minggu 2'],

            // Perawatan & Renovasi
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katRenovasi->id, 'tanggal_transaksi' => '2024-02-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 3500000, 'deskripsi' => 'Pengecatan ulang tembok masjid'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katRenovasi->id, 'tanggal_transaksi' => '2024-03-15', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1200000, 'deskripsi' => 'Perbaikan keran tempat wudhu'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katRenovasi->id, 'tanggal_transaksi' => '2024-04-20', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 800000,  'deskripsi' => 'Servis AC split ruang utama'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katRenovasi->id, 'tanggal_transaksi' => '2024-05-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 650000,  'deskripsi' => 'Perbaikan instalasi lampu selasar'],

            // Perlengkapan Ibadah
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katPerlengkapan->id, 'tanggal_transaksi' => '2024-03-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1500000, 'deskripsi' => 'Pembelian sajadah tambahan (10 pcs)'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katPerlengkapan->id, 'tanggal_transaksi' => '2024-03-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 600000,  'deskripsi' => 'Pembelian tasbih & perlengkapan ibadah'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katPerlengkapan->id, 'tanggal_transaksi' => '2024-04-15', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 450000,  'deskripsi' => 'Pembelian Al-Quran untuk jamaah (5 buah)'],

            // Sosial & Santunan
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katSosial->id, 'tanggal_transaksi' => '2024-01-20', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1000000, 'deskripsi' => 'Santunan anak yatim Januari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katSosial->id, 'tanggal_transaksi' => '2024-02-20', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1000000, 'deskripsi' => 'Santunan anak yatim Februari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katSosial->id, 'tanggal_transaksi' => '2024-03-27', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2500000, 'deskripsi' => 'Santunan anak yatim malam 27 Ramadhan'],

            // Pembelian Aset
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katAset->id, 'tanggal_transaksi' => '2019-06-20', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 18000000,  'deskripsi' => 'Pembelian Sound System & Amplifier TOA'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katAset->id, 'tanggal_transaksi' => '2018-10-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 280000000, 'deskripsi' => 'Pembelian Mobil Jenazah Toyota HiAce'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katAset->id, 'tanggal_transaksi' => '2020-07-15', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 28000000,  'deskripsi' => 'Pembelian AC Split 2 PK (4 Unit)'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katAset->id, 'tanggal_transaksi' => '2017-09-01', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 4800000,   'deskripsi' => 'Pembelian Kipas Angin Gantung (6 Unit)'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katAset->id, 'tanggal_transaksi' => '2019-03-12', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 5500000,   'deskripsi' => 'Pembelian Mesin Pompa Air'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katAset->id, 'tanggal_transaksi' => '2020-12-01', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 12000000,  'deskripsi' => 'Pembelian Generator Listrik 5000 Watt'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katAset->id, 'tanggal_transaksi' => '2021-01-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 3500000,   'deskripsi' => 'Pembelian Jam Digital Jadwal Sholat'],
        ];

        foreach ($transaksiiBendahara as $item) {
            Transaksi::create(array_merge($item, [
                'user_id'         => $user->id,
                'catatan'         => null,
                'status_approval' => null,    
                'status_jurnal'   => 'MAPPED', 
            ]));
        }

        // ══════════════════════════════════════════════════════
        // TRANSAKSI DARI INFAK/KENCLENG (status_approval = APPROVED)
        // status_jurnal = MAPPED 
        // ══════════════════════════════════════════════════════
        $transaksiApproved = [

            // Infak Jumat
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakJumat->id, 'tanggal_transaksi' => '2024-01-05', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2350000, 'deskripsi' => 'Infak Jumat minggu pertama Januari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakJumat->id, 'tanggal_transaksi' => '2024-01-12', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1980000, 'deskripsi' => 'Infak Jumat minggu kedua Januari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakJumat->id, 'tanggal_transaksi' => '2024-01-19', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2100000, 'deskripsi' => 'Infak Jumat minggu ketiga Januari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakJumat->id, 'tanggal_transaksi' => '2024-01-26', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2250000, 'deskripsi' => 'Infak Jumat minggu keempat Januari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakJumat->id, 'tanggal_transaksi' => '2024-02-02', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2150000, 'deskripsi' => 'Infak Jumat minggu pertama Februari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakJumat->id, 'tanggal_transaksi' => '2024-02-09', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1900000, 'deskripsi' => 'Infak Jumat minggu kedua Februari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakJumat->id, 'tanggal_transaksi' => '2024-02-16', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2050000, 'deskripsi' => 'Infak Jumat minggu ketiga Februari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakJumat->id, 'tanggal_transaksi' => '2024-03-01', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2400000, 'deskripsi' => 'Infak Jumat minggu pertama Maret'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakJumat->id, 'tanggal_transaksi' => '2024-04-05', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 3100000, 'deskripsi' => 'Infak Jumat bulan Ramadhan'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakJumat->id, 'tanggal_transaksi' => '2024-05-03', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2200000, 'deskripsi' => 'Infak Jumat minggu pertama Mei'],

            // Infak Harian
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2024-01-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1250000, 'deskripsi' => 'Rekapitulasi infak harian Januari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2024-02-29', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1100000, 'deskripsi' => 'Rekapitulasi infak harian Februari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2024-03-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1850000, 'deskripsi' => 'Rekapitulasi infak harian Maret (Ramadhan)'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2024-04-30', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 980000,  'deskripsi' => 'Rekapitulasi infak harian April'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2024-05-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1050000, 'deskripsi' => 'Rekapitulasi infak harian Mei'],

            // Kencleng (auto-mapped oleh backend)
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katKencleng->id, 'tanggal_transaksi' => '2024-01-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 875000,  'deskripsi' => 'Rekapitulasi kencleng Januari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katKencleng->id, 'tanggal_transaksi' => '2024-02-29', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 920000,  'deskripsi' => 'Rekapitulasi kencleng Februari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katKencleng->id, 'tanggal_transaksi' => '2024-03-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1350000, 'deskripsi' => 'Rekapitulasi kencleng Maret (Ramadhan)'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katKencleng->id, 'tanggal_transaksi' => '2024-04-30', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 810000,  'deskripsi' => 'Rekapitulasi kencleng April'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => null, 'kategori_transaksi_id' => $katKencleng->id, 'tanggal_transaksi' => '2024-05-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 890000,  'deskripsi' => 'Rekapitulasi kencleng Mei'],

            // Transaksi kegiatan APPROVED + MAPPED
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => $kegKajian->id, 'kategori_transaksi_id' => $katHonor->id,    'tanggal_transaksi' => '2024-01-07', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 750000,  'deskripsi' => 'Honor ustadz kajian Ahad pagi Januari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => $kegKajian->id, 'kategori_transaksi_id' => $katHonor->id,    'tanggal_transaksi' => '2024-02-04', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 750000,  'deskripsi' => 'Honor ustadz kajian Ahad pagi Februari'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => $kegKajian->id, 'kategori_transaksi_id' => $katKonsumsi->id ?? $katOperasional->id, 'tanggal_transaksi' => '2024-03-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,  'deskripsi' => 'Konsumsi kajian Ramadhan'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => $kegQurban->id, 'kategori_transaksi_id' => $katDonasi->id,   'tanggal_transaksi' => '2024-05-20', 'jenis_transaksi' => 'PEMASUKAN',   'jumlah' => 15000000,'deskripsi' => 'Donasi qurban dari jamaah'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => $kegZakat->id,  'kategori_transaksi_id' => $katDonasi->id,   'tanggal_transaksi' => '2024-04-01', 'jenis_transaksi' => 'PEMASUKAN',   'jumlah' => 8500000, 'deskripsi' => 'Penerimaan zakat fitrah'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => $kegSosial->id, 'kategori_transaksi_id' => $katSosial->id,   'tanggal_transaksi' => '2024-05-15', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 3000000, 'deskripsi' => 'Belanja sembako bakti sosial'],

            // Transaksi kegiatan APPROVED + UNMAPPED (perlu mapping oleh bendahara)
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => $kegQurban->id, 'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2024-06-01', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 12000000,'deskripsi' => 'Pembelian hewan qurban sapi 2 ekor', 'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => $kegSosial->id, 'kategori_transaksi_id' => $katSosial->id,   'tanggal_transaksi' => '2024-06-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1500000, 'deskripsi' => 'Transportasi tim bakti sosial',     'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kasKotak->id, 'kegiatan_id' => $kegZakat->id,  'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2024-06-08', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 7200000, 'deskripsi' => 'Distribusi zakat fitrah ke mustahiq',  'status_jurnal' => 'UNMAPPED'],
        ];

        foreach ($transaksiApproved as $item) {
            Transaksi::create(array_merge([
                'user_id'         => $user->id,
                'catatan'         => null,
                'status_approval' => 'APPROVED',
                'status_jurnal'   => 'MAPPED', 
            ], $item));
        }

        // ══════════════════════════════════════════════════════
        // TRANSAKSI KEGIATAN UNTUK FITUR APPROVAL
        // (PENDING, REVISION, REJECTED — tidak muncul di tabel transaksi)
        // ══════════════════════════════════════════════════════
        $transaksiApproval = [
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => $kegQurban->id, 'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2024-06-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 5000000,  'deskripsi' => 'Sewa tenda dan kursi acara qurban',         'catatan' => 'Menunggu persetujuan bendahara', 'status_approval' => 'PENDING',  'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => $kegQurban->id, 'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2024-06-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 800000,   'deskripsi' => 'Pembelian bumbu dan peralatan masak qurban', 'catatan' => null,                            'status_approval' => 'PENDING',  'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => $kegSosial->id, 'kategori_transaksi_id' => $katSosial->id,   'tanggal_transaksi' => '2024-06-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2500000,  'deskripsi' => 'Pembelian paket sembako bakti sosial',      'catatan' => null,                            'status_approval' => 'PENDING',  'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => $kegKajian->id, 'kategori_transaksi_id' => $katHonor->id,    'tanggal_transaksi' => '2024-05-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1500000,  'deskripsi' => 'Honor ustadz kajian spesial Idul Fitri',    'catatan' => 'Direvisi: nominal terlalu besar', 'status_approval' => 'REVISION', 'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => $kegZakat->id,  'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2024-04-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,   'deskripsi' => 'Pembelian kantong plastik distribusi zakat', 'catatan' => 'Ditolak: sudah ada stok',        'status_approval' => 'REJECTED', 'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => $kegQurban->id, 'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2024-06-12', 'jenis_transaksi' => 'PEMASUKAN',  'jumlah' => 3000000,  'deskripsi' => 'Donasi tambahan untuk qurban dari donatur',  'catatan' => null,                            'status_approval' => 'PENDING',  'status_jurnal' => 'UNMAPPED'],
        ];

        foreach ($transaksiApproval as $item) {
            Transaksi::create(array_merge(['user_id' => $user->id], $item));
        }
    }
}