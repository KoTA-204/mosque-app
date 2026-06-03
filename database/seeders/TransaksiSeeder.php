<?php
// Database/Seeders/TransaksiSeeder.php

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
        $user    = User::first();

        // Dompet
        $kasKotak      = Dompet::where('nama_dompet', 'Kas Masjid')->first();
        $rekOperasional= Dompet::where('nama_dompet', 'Bank BSI Operasional')->first();

        // Kategori
        $katInfakJumat  = KategoriTransaksi::where('nama_kategori', 'Infak Jumat')->first();
        $katInfakHarian = KategoriTransaksi::where('nama_kategori', 'Infak Harian')->first();
        $katSedekah     = KategoriTransaksi::where('nama_kategori', 'Donasi Qurban')->first();
        $katZakat       = KategoriTransaksi::where('nama_kategori', 'Zakat')->first();
        $katDonasi      = KategoriTransaksi::where('nama_kategori', 'Donasi Kegiatan')->first();
        $katKencleng    = KategoriTransaksi::where('nama_kategori', 'Kencleng')->first();
        $katOperasional = KategoriTransaksi::where('nama_kategori', 'Operasional Masjid')->first();
        $katAset        = KategoriTransaksi::where('nama_kategori', 'Pembelian Aset')->first();
        $katRenovasi    = KategoriTransaksi::where('nama_kategori', 'Perawatan & Renovasi')->first();
        $katHonor       = KategoriTransaksi::where('nama_kategori', 'Honorarium')->first();
        $katKonsumsi    = KategoriTransaksi::where('nama_kategori', 'Konsumsi')->first();
        $katPerlengkapan= KategoriTransaksi::where('nama_kategori', 'Perlengkapan Ibadah')->first();
        $katSosial      = KategoriTransaksi::where('nama_kategori', 'Sosial & Santunan')->first();
        $katKegiatan    = KategoriTransaksi::where('nama_kategori', 'Kegiatan')->first();

        // Kegiatan
        $kegQurban  = Kegiatan::where('nama_kegiatan', 'Qurban 1447 H')->first();
        $kegZakat   = Kegiatan::where('nama_kegiatan', 'Zakat Fitrah 1447 H')->first();
        $kegKajian  = Kegiatan::where('nama_kegiatan', 'Kajian Ramadan 1447 H')->first();
        $kegSosial  = Kegiatan::where('nama_kegiatan', 'Bakti Sosial Idul Adha 1447 H')->first();

        $data = [
            // ══════════════════════════════════════════════════════
            // PEMASUKAN RUTIN
            // ══════════════════════════════════════════════════════

            // Infak Jumat
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-01-05', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 2350000,  'deskripsi' => 'Infak Jumat minggu pertama Januari',    'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-01-12', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 1980000,  'deskripsi' => 'Infak Jumat minggu kedua Januari',      'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-01-19', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 2100000,  'deskripsi' => 'Infak Jumat minggu ketiga Januari',     'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-01-26', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 2250000,  'deskripsi' => 'Infak Jumat minggu keempat Januari',    'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-02-02', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 2150000,  'deskripsi' => 'Infak Jumat minggu pertama Februari',   'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-02-09', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 1900000,  'deskripsi' => 'Infak Jumat minggu kedua Februari',     'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-03-01', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 2400000,  'deskripsi' => 'Infak Jumat minggu pertama Maret',      'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-04-05', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 3100000,  'deskripsi' => 'Infak Jumat bulan Ramadhan',            'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-05-03', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 2200000,  'deskripsi' => 'Infak Jumat minggu pertama Mei',        'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-06-07', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 2050000,  'deskripsi' => 'Infak Jumat minggu pertama Juni',       'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'UNMAPPED'],

            // Infak Harian & Kencleng
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2024-01-31', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 1250000,  'deskripsi' => 'Rekapitulasi infak harian Januari',     'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2024-02-29', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 1100000,  'deskripsi' => 'Rekapitulasi infak harian Februari',    'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2024-03-31', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 1850000,  'deskripsi' => 'Rekapitulasi infak harian Maret (Ramadhan)', 'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katKencleng->id,    'tanggal_transaksi' => '2024-01-31', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 875000,    'deskripsi' => 'Rekapitulasi kencleng Januari',         'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katKencleng->id,    'tanggal_transaksi' => '2024-02-29', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 920000,    'deskripsi' => 'Rekapitulasi kencleng Februari',        'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katKencleng->id,    'tanggal_transaksi' => '2024-03-31', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 1350000,  'deskripsi' => 'Rekapitulasi kencleng Maret (Ramadhan)', 'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],

            // ══════════════════════════════════════════════════════
            // PENGELUARAN RUTIN
            // ══════════════════════════════════════════════════════

            // Operasional
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-01-05', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 850000,    'deskripsi' => 'Tagihan listrik Januari',               'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-01-05', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 250000,    'deskripsi' => 'Tagihan air PDAM Januari',              'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-02-05', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 900000,    'deskripsi' => 'Tagihan listrik Februari',              'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-02-05', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 230000,    'deskripsi' => 'Tagihan air PDAM Februari',             'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-03-05', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 1100000,  'deskripsi' => 'Tagihan listrik Maret (Ramadhan)',       'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-01-15', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 300000,    'deskripsi' => 'Pembelian alat kebersihan masjid',      'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-02-15', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 275000,    'deskripsi' => 'Pembelian sabun dan pewangi masjid',    'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],

            // Honorarium
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katHonor->id,       'tanggal_transaksi' => '2024-01-31', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 2000000,  'deskripsi' => 'Honor imam & marbot Januari',           'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katHonor->id,       'tanggal_transaksi' => '2024-02-29', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 2000000,  'deskripsi' => 'Honor imam & marbot Februari',          'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katHonor->id,       'tanggal_transaksi' => '2024-03-31', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 3000000,  'deskripsi' => 'Honor imam tarawih & marbot Ramadhan',  'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katHonor->id,       'tanggal_transaksi' => '2024-01-07', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 500000,    'deskripsi' => 'Honor khatib Jumat Januari minggu 1',   'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katHonor->id,       'tanggal_transaksi' => '2024-01-14', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 500000,    'deskripsi' => 'Honor khatib Jumat Januari minggu 2',   'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => $kegKajian->id, 'user_id' => $user->id, 'kategori_transaksi_id' => $katHonor->id,       'tanggal_transaksi' => '2024-01-07', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 750000,    'deskripsi' => 'Honor ustadz kajian Ahad pagi',         'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => $kegKajian->id, 'user_id' => $user->id, 'kategori_transaksi_id' => $katHonor->id,       'tanggal_transaksi' => '2024-02-04', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 750000,    'deskripsi' => 'Honor ustadz kajian Ahad pagi Februari','catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],

            // Perawatan & Renovasi
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katRenovasi->id,    'tanggal_transaksi' => '2024-02-10', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 3500000,  'deskripsi' => 'Pengecatan ulang tembok masjid',        'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katRenovasi->id,    'tanggal_transaksi' => '2024-03-15', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 1200000,  'deskripsi' => 'Perbaikan keran tempat wudhu',          'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katRenovasi->id,    'tanggal_transaksi' => '2024-04-20', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 800000,    'deskripsi' => 'Servis AC split ruang utama',           'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'UNMAPPED'],

            // Perlengkapan Ibadah
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katPerlengkapan->id,'tanggal_transaksi' => '2024-03-05', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 1500000,  'deskripsi' => 'Pembelian sajadah tambahan (10 pcs)',    'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katPerlengkapan->id,'tanggal_transaksi' => '2024-03-10', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 600000,    'deskripsi' => 'Pembelian tasbih & perlengkapan ibadah','catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],

            // ══════════════════════════════════════════════════════
            // PENGELUARAN PEMBELIAN ASET (dipakai di AsetSeeder)
            // ══════════════════════════════════════════════════════
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katAset->id,        'tanggal_transaksi' => '2019-06-20', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 18000000, 'deskripsi' => 'Pembelian Sound System & Amplifier TOA','catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katAset->id,        'tanggal_transaksi' => '2018-10-10', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 280000000,'deskripsi' => 'Pembelian Mobil Jenazah Toyota HiAce',  'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katAset->id,        'tanggal_transaksi' => '2020-07-15', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 28000000, 'deskripsi' => 'Pembelian AC Split 2 PK (4 Unit)',      'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katAset->id,        'tanggal_transaksi' => '2017-09-01', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 4800000,  'deskripsi' => 'Pembelian Kipas Angin Gantung (6 Unit)', 'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katAset->id,        'tanggal_transaksi' => '2019-03-12', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 5500000,  'deskripsi' => 'Pembelian Mesin Pompa Air',             'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katAset->id,        'tanggal_transaksi' => '2020-12-01', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 12000000, 'deskripsi' => 'Pembelian Generator Listrik 5000 Watt', 'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katAset->id,        'tanggal_transaksi' => '2021-01-10', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 3500000,  'deskripsi' => 'Pembelian Jam Digital Jadwal Sholat',   'catatan' => null, 'status_approval' => 'APPROVED', 'status_jurnal' => 'MAPPED'],

            // ══════════════════════════════════════════════════════
            // TRANSAKSI PENDING & DRAFT (untuk testing approval)
            // ══════════════════════════════════════════════════════
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katRenovasi->id,    'tanggal_transaksi' => '2024-06-01', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 15000000, 'deskripsi' => 'Renovasi toilet & tempat wudhu',        'catatan' => 'Menunggu persetujuan bendahara', 'status_approval' => 'PENDING',  'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2024-06-03', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 450000,    'deskripsi' => 'Pembelian lampu LED pengganti',         'catatan' => null,                            'status_approval' => 'PENDING',  'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katAset->id,        'tanggal_transaksi' => '2024-06-05', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 8500000,  'deskripsi' => 'Pembelian CCTV System (8 Kamera)',      'catatan' => 'Menunggu persetujuan',           'status_approval' => 'DRAFT',    'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kasKotak->id,       'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2024-06-07', 'jenis_transaksi' => 'PEMASUKAN',    'jumlah' => 2300000,  'deskripsi' => 'Infak Jumat minggu pertama Juni',       'catatan' => null,                            'status_approval' => 'PENDING',  'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $rekOperasional->id, 'kegiatan_id' => null,           'user_id' => $user->id, 'kategori_transaksi_id' => $katRenovasi->id,    'tanggal_transaksi' => '2024-05-20', 'jenis_transaksi' => 'PENGELUARAN',  'jumlah' => 5000000,  'deskripsi' => 'Perbaikan atap masjid bocor',           'catatan' => 'Direvisi: lampirkan nota',       'status_approval' => 'REVISION', 'status_jurnal' => 'UNMAPPED'],
        ];

        foreach ($data as $item) {
            Transaksi::create($item);
        }
    }
}