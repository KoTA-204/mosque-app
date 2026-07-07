<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;
use App\Models\Transaksi;
use App\Models\Kegiatan;
use App\Models\Dompet;
use App\Models\User;
use App\Models\KategoriTransaksi;

/**
 * Transaksi demo TAHUN BERJALAN (2026) — SETELAH Jurnal Pembuka.
 *
 * Prinsip:
 *  - Aset historis (perolehan sebelum periode berjalan) TIDAK dicatat di sini.
 *    Nilainya sudah ada di Jurnal Pembuka (JurnalLengkapSeeder: 1-201/1-202/1-206
 *    + akumulasi penyusutan) dan di register aset (AsetSeeder, transaksi_id = null).
 *  - Hanya pembelian aset SETELAH pembukaan yang dicatat sebagai transaksi
 *    (kategori "Pembelian Aset") — meniru alur aplikasi TransaksiService@simpanAsetDariTransaksi
 *    yang otomatis membuat record Aset. Aset barunya dibuat & ditautkan di AsetSeeder.
 *  - Transaksi disebar ke 3 dompet (Kas / BSI / BRI) dengan pemasukan & pengeluaran
 *    yang seimbang supaya saldo tiap dompet tetap positif dan "merata".
 */
class TransaksiSeeder extends Seeder
{
    /**
     * Transaksi NON-kegiatan:
     *  (A) Infak kencleng  -> lewat approval (status_approval = APPROVED), punya record kencleng.
     *  (B) Pencatatan bendahara langsung -> status_approval = null (tidak butuh approval).
     *
     * Kondisi mapping akun:
     *  - status_jurnal = MAPPED   -> sudah dipetakan, ada jurnal umum (lihat JurnalUmumSeeder).
     *  - status_jurnal = UNMAPPED -> belum dipetakan bendahara, belum punya jurnal.
     *
     * no_referensi dipakai sebagai kunci stabil oleh seeder lain (Kencleng, Bukti, JurnalUmum).
     */
    public function run(): void
    {
        $user = User::first();
        $kas  = Dompet::where('nama_dompet', 'Kas Masjid')->first();
        $bsi  = Dompet::where('nama_dompet', 'Bank BSI Operasional')->first();
        $bri  = Dompet::where('nama_dompet', 'Bank BRI Operasional')->first();

        $katInfakJumat   = KategoriTransaksi::where('nama_kategori', 'Infak Jumat')->first();
        $katInfakHarian  = KategoriTransaksi::where('nama_kategori', 'Infak Harian')->first();
        $katKencleng     = KategoriTransaksi::where('nama_kategori', 'Kencleng')->first();
        $katSedekah      = KategoriTransaksi::where('nama_kategori', 'Sedekah')->first();
        $katWakaf        = KategoriTransaksi::where('nama_kategori', 'Wakaf')->first();
        $katZakat        = KategoriTransaksi::where('nama_kategori', 'Zakat')->first();
        $katOperasional  = KategoriTransaksi::where('nama_kategori', 'Operasional Masjid')->first();
        $katAset         = KategoriTransaksi::where('nama_kategori', 'Pembelian Aset')->first();
        $katRenovasi     = KategoriTransaksi::where('nama_kategori', 'Perawatan & Renovasi')->first();
        $katHonor        = KategoriTransaksi::where('nama_kategori', 'Honorarium')->first();
        $katPerlengkapan = KategoriTransaksi::where('nama_kategori', 'Perlengkapan Ibadah')->first();
        $katSosial       = KategoriTransaksi::where('nama_kategori', 'Sosial & Santunan')->first();
        $katKegiatan     = KategoriTransaksi::where('nama_kategori', 'Kegiatan')->first();
        $katDonasi       = KategoriTransaksi::where('nama_kategori', 'Donasi Kegiatan')->first();
        $katKonsumsi     = KategoriTransaksi::where('nama_kategori', 'Konsumsi')->first();
        $katLainnya      = KategoriTransaksi::where('nama_kategori', 'Lainnya')->first();

        $kegQurban = Kegiatan::where('nama_kegiatan', 'Qurban 1447 H')->first();
        $kegZakat  = Kegiatan::where('nama_kegiatan', 'Zakat Fitrah 1447 H')->first();
        $kegKajian = Kegiatan::where('nama_kegiatan', 'Kajian Ramadan 1447 H')->first();
        $kegSosial = Kegiatan::where('nama_kegiatan', 'Bakti Sosial Idul Adha 1447 H')->first();

        // ══════════════════════════════════════════════════════════════════
        // TRANSAKSI TERCATAT (status_approval APPROVED, status_jurnal MAPPED)
        // ══════════════════════════════════════════════════════════════════
        $transaksi = [
            // ─── KAS MASJID · PEMASUKAN (infak & kencleng tunai) ───
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2026-01-02', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2350000, 'deskripsi' => 'Infak Jumat minggu pertama Januari'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2026-02-06', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2150000, 'deskripsi' => 'Infak Jumat minggu pertama Februari'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2026-03-06', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2400000, 'deskripsi' => 'Infak Jumat minggu pertama Maret'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2026-04-03', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 3100000, 'deskripsi' => 'Infak Jumat bulan Ramadhan April'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2026-05-01', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2200000, 'deskripsi' => 'Infak Jumat minggu pertama Mei'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakJumat->id,  'tanggal_transaksi' => '2026-06-05', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 2500000, 'deskripsi' => 'Infak Jumat minggu pertama Juni'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2026-01-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1250000, 'deskripsi' => 'Rekapitulasi infak harian Januari'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2026-02-28', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1100000, 'deskripsi' => 'Rekapitulasi infak harian Februari'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2026-03-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1850000, 'deskripsi' => 'Rekapitulasi infak harian Maret'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2026-04-30', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 980000,  'deskripsi' => 'Rekapitulasi infak harian April'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katInfakHarian->id, 'tanggal_transaksi' => '2026-05-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1050000, 'deskripsi' => 'Rekapitulasi infak harian Mei'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katKencleng->id,    'tanggal_transaksi' => '2026-01-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 875000,  'deskripsi' => 'Rekapitulasi kencleng Januari'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katKencleng->id,    'tanggal_transaksi' => '2026-02-28', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 920000,  'deskripsi' => 'Rekapitulasi kencleng Februari'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katKencleng->id,    'tanggal_transaksi' => '2026-03-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 1350000, 'deskripsi' => 'Rekapitulasi kencleng Maret'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katKencleng->id,    'tanggal_transaksi' => '2026-04-30', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 810000,  'deskripsi' => 'Rekapitulasi kencleng April'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katKencleng->id,    'tanggal_transaksi' => '2026-05-31', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 890000,  'deskripsi' => 'Rekapitulasi kencleng Mei'],

            // ─── KAS MASJID · PENGELUARAN (honor, perlengkapan, sosial, kebersihan) ───
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katHonor->id,        'tanggal_transaksi' => '2026-01-31', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2000000, 'deskripsi' => 'Honor imam & marbot Januari'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katHonor->id,        'tanggal_transaksi' => '2026-02-28', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2000000, 'deskripsi' => 'Honor imam & marbot Februari'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katHonor->id,        'tanggal_transaksi' => '2026-03-31', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 3000000, 'deskripsi' => 'Honor imam tarawih & marbot Ramadhan'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katHonor->id,        'tanggal_transaksi' => '2026-04-30', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2000000, 'deskripsi' => 'Honor imam & marbot April'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katHonor->id,        'tanggal_transaksi' => '2026-05-31', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2000000, 'deskripsi' => 'Honor imam & marbot Mei'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katHonor->id,        'tanggal_transaksi' => '2026-01-09', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,  'deskripsi' => 'Honor khatib Jumat Januari minggu 1'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katHonor->id,        'tanggal_transaksi' => '2026-01-16', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,  'deskripsi' => 'Honor khatib Jumat Januari minggu 2'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katPerlengkapan->id, 'tanggal_transaksi' => '2026-03-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1500000, 'deskripsi' => 'Pembelian sajadah tambahan (10 pcs)'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katPerlengkapan->id, 'tanggal_transaksi' => '2026-04-15', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 450000,  'deskripsi' => 'Pembelian Al-Quran untuk jamaah (5 buah)'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katSosial->id,       'tanggal_transaksi' => '2026-01-20', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1000000, 'deskripsi' => 'Santunan anak yatim Januari'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katSosial->id,       'tanggal_transaksi' => '2026-02-20', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1000000, 'deskripsi' => 'Santunan anak yatim Februari'],
            ['dompet_id' => $kas->id, 'kategori_transaksi_id' => $katOperasional->id,  'tanggal_transaksi' => '2026-01-15', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 300000,  'deskripsi' => 'Pembelian alat kebersihan masjid'],

            // ─── BANK BSI · PEMASUKAN (donasi/zakat/wakaf via transfer) ───
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katSedekah->id, 'tanggal_transaksi' => '2026-02-10', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 25000000, 'deskripsi' => 'Donasi pembangunan via transfer BSI'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katZakat->id,   'tanggal_transaksi' => '2026-03-12', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 15000000, 'deskripsi' => 'Penerimaan zakat maal via transfer BSI'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katWakaf->id,   'tanggal_transaksi' => '2026-04-08', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 20000000, 'deskripsi' => 'Wakaf tunai via transfer BSI'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katSedekah->id, 'tanggal_transaksi' => '2026-05-14', 'jenis_transaksi' => 'PEMASUKAN', 'jumlah' => 10000000, 'deskripsi' => 'Donasi rutin donatur tetap via transfer BSI'],

            // ─── BANK BSI · PENGELUARAN (tagihan, renovasi, pembelian aset baru) ───
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2026-01-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 850000,  'deskripsi' => 'Tagihan listrik Januari'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2026-02-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 900000,  'deskripsi' => 'Tagihan listrik Februari'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2026-03-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1100000, 'deskripsi' => 'Tagihan listrik Maret (Ramadhan)'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2026-04-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 870000,  'deskripsi' => 'Tagihan listrik April'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2026-01-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 250000,  'deskripsi' => 'Tagihan air PDAM Januari'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2026-02-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 230000,  'deskripsi' => 'Tagihan air PDAM Februari'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katOperasional->id, 'tanggal_transaksi' => '2026-03-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 260000,  'deskripsi' => 'Tagihan air PDAM Maret'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katRenovasi->id,    'tanggal_transaksi' => '2026-02-12', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 3500000, 'deskripsi' => 'Pengecatan ulang tembok masjid'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katRenovasi->id,    'tanggal_transaksi' => '2026-03-18', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1200000, 'deskripsi' => 'Perbaikan keran tempat wudhu'],
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katRenovasi->id,    'tanggal_transaksi' => '2026-04-22', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 800000,  'deskripsi' => 'Servis AC split ruang utama'],
            // Pembelian aset SETELAH pembukaan → otomatis jadi aset (ditautkan di AsetSeeder)
            ['dompet_id' => $bsi->id, 'kategori_transaksi_id' => $katAset->id,        'tanggal_transaksi' => '2026-05-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 18000000, 'deskripsi' => 'Pembelian Laptop & Printer Sekretariat'],

            // ─── BANK BRI · PEMASUKAN & PENGELUARAN (program & administrasi) ───
            ['dompet_id' => $bri->id, 'kategori_transaksi_id' => $katSedekah->id,     'tanggal_transaksi' => '2026-02-18', 'jenis_transaksi' => 'PEMASUKAN',  'jumlah' => 15000000, 'deskripsi' => 'Donasi CSR perusahaan via transfer BRI'],
            ['dompet_id' => $bri->id, 'kategori_transaksi_id' => $katDonasi->id,      'tanggal_transaksi' => '2026-03-25', 'jenis_transaksi' => 'PEMASUKAN',  'jumlah' => 10000000, 'deskripsi' => 'Donasi program beasiswa santri via transfer BRI'],
            ['dompet_id' => $bri->id, 'kategori_transaksi_id' => $katHonor->id,       'tanggal_transaksi' => '2026-04-28', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 2000000,  'deskripsi' => 'Honor pengajar TPA via transfer BRI'],
            ['dompet_id' => $bri->id, 'kategori_transaksi_id' => $katSosial->id,      'tanggal_transaksi' => '2026-05-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 5000000,  'deskripsi' => 'Pencairan beasiswa santri berprestasi'],
            ['dompet_id' => $bri->id, 'kategori_transaksi_id' => $katLainnya->id,     'tanggal_transaksi' => '2026-05-31', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 150000,   'deskripsi' => 'Biaya administrasi bank BRI'],

            // ─── KEGIATAN · APPROVED + MAPPED ───
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegKajian->id, 'kategori_transaksi_id' => $katHonor->id,   'tanggal_transaksi' => '2026-01-12', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 750000,   'deskripsi' => 'Honor ustadz kajian Ahad pagi Januari'],
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegKajian->id, 'kategori_transaksi_id' => $katKonsumsi->id, 'tanggal_transaksi' => '2026-03-15', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,   'deskripsi' => 'Konsumsi kajian Ramadhan'],
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegQurban->id, 'kategori_transaksi_id' => $katDonasi->id,  'tanggal_transaksi' => '2026-05-20', 'jenis_transaksi' => 'PEMASUKAN',   'jumlah' => 15000000, 'deskripsi' => 'Donasi qurban dari jamaah'],
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegZakat->id,  'kategori_transaksi_id' => $katDonasi->id,  'tanggal_transaksi' => '2026-04-01', 'jenis_transaksi' => 'PEMASUKAN',   'jumlah' => 8500000,  'deskripsi' => 'Penerimaan zakat fitrah'],
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegSosial->id, 'kategori_transaksi_id' => $katSosial->id,  'tanggal_transaksi' => '2026-05-15', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 3000000,  'deskripsi' => 'Belanja sembako bakti sosial'],

            // ─── KEGIATAN · APPROVED + UNMAPPED (perlu mapping bendahara) ───
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegQurban->id, 'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2026-06-01', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 12000000, 'deskripsi' => 'Pembelian hewan qurban sapi 2 ekor', 'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegSosial->id, 'kategori_transaksi_id' => $katSosial->id,   'tanggal_transaksi' => '2026-06-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1500000,  'deskripsi' => 'Transportasi tim bakti sosial',     'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegZakat->id,  'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2026-04-06', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 7200000,  'deskripsi' => 'Distribusi zakat fitrah ke mustahiq',  'status_jurnal' => 'UNMAPPED'],
        ];

        foreach ($transaksi as $item) {
            Transaksi::create(array_merge([
                'user_id'         => $user->id,
                'catatan'         => null,
                'status_approval' => 'APPROVED',
                'status_jurnal'   => 'MAPPED',
            ], $item));
        }

        // ══════════════════════════════════════════════════════════════════
        // TRANSAKSI UNTUK FITUR APPROVAL (PENDING/REVISION/REJECTED)
        // Tidak memengaruhi saldo dompet (dashboard hanya menghitung APPROVED/null).
        // ══════════════════════════════════════════════════════════════════
        $transaksiApproval = [
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegQurban->id, 'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2026-06-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 5000000, 'deskripsi' => 'Sewa tenda dan kursi acara qurban',         'catatan' => 'Menunggu persetujuan bendahara', 'status_approval' => 'PENDING',  'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegQurban->id, 'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2026-06-10', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 800000,  'deskripsi' => 'Pembelian bumbu dan peralatan masak qurban', 'catatan' => null,                            'status_approval' => 'PENDING',  'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $bsi->id, 'kegiatan_id' => $kegKajian->id, 'kategori_transaksi_id' => $katHonor->id,    'tanggal_transaksi' => '2026-05-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 1500000, 'deskripsi' => 'Honor ustadz kajian spesial Idul Fitri',    'catatan' => 'Direvisi: nominal terlalu besar', 'status_approval' => 'REVISION', 'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $kas->id, 'kegiatan_id' => $kegZakat->id,  'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2026-04-05', 'jenis_transaksi' => 'PENGELUARAN', 'jumlah' => 500000,  'deskripsi' => 'Pembelian kantong plastik distribusi zakat', 'catatan' => 'Ditolak: sudah ada stok',        'status_approval' => 'REJECTED', 'status_jurnal' => 'UNMAPPED'],
            ['dompet_id' => $bsi->id, 'kegiatan_id' => $kegQurban->id, 'kategori_transaksi_id' => $katKegiatan->id, 'tanggal_transaksi' => '2026-06-12', 'jenis_transaksi' => 'PEMASUKAN',  'jumlah' => 3000000, 'deskripsi' => 'Donasi tambahan untuk qurban dari donatur',  'catatan' => null,                            'status_approval' => 'PENDING',  'status_jurnal' => 'UNMAPPED'],
        ];

        foreach ($transaksiApproval as $row) {
            Transaksi::create($row + ['user_id' => $user->id]);
        }
    }
}

