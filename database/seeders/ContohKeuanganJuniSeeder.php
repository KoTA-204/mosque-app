<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Aset;
use App\Models\DetailJurnal;
use App\Models\Dompet;
use App\Models\Jurnal;
use App\Models\KategoriTransaksi;
use App\Models\Periode;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ContohKeuanganJuniSeeder
 * ------------------------------------------------------------------
 * Contoh data keuangan Masjid Lukmanul Hakim yang RINGKAS & bernominal KECIL,
 * untuk memverifikasi algoritma satu siklus akuntansi.
 *
 * Skenario:
 *  JUNI 2026 (periode ditutup):
 *   - Jurnal Pembuka (1 Juni) -> awal pencatatan akuntansi.
 *   - Beberapa Jurnal Umum (pemasukan, pengeluaran, & penyaluran zakat).
 *   - Jurnal Penyesuaian penyusutan aset (30 Juni) - SEMUA aset yang
 *     disusutkan sudah punya entri penyusutan (syarat tutup terpenuhi).
 *   - Jurnal Penutup (30 Juni): Tutup Pendapatan, Tutup Beban,
 *     Pelepasan Pembatasan -> lalu periode Juni ditutup (status=false).
 *
 *  JULI 2026 (periode AKTIF, status=true):
 *   - 3 transaksi/Jurnal Umum baru.
 *   - 1 Jurnal KOREKSI yang memperbaiki jurnal Juni (menunjuk jurnal Juni
 *     lewat jurnal_ref_id). Koreksi WAJIB dicatat di periode aktif (Juli),
 *     karena periode Juni yang sudah tutup tidak boleh dicatati lagi.
 *
 * Semua jurnal POSTED & seimbang.
 * Posisi Keuangan akhir Juni balance: Total Aset = Aset Neto = Rp 49.150.000
 *
 * PRASYARAT: RoleSeeder, UserSeeder, KategoriAkunSeeder, AkunSeeder.
 *   (KategoriTransaksiSeeder TIDAK diperlukan: semua transaksi di sini adalah
 *    transaksi umum tanpa kegiatan, sehingga kategori_transaksi_id = NULL.
 *    Kategori transaksi hanya wajib untuk transaksi kegiatan.)
 *
 * Cara pakai:
 *   php artisan db:seed --class=Database\\Seeders\\ContohKeuanganJuniSeeder
 *
 * CATATAN: Jurnal Pembuka 1 Juni sengaja lewat seeder karena UI
 * (JurnalPembukaService@pastikanTanggalTidakBerlalu) memblokir tanggal lampau.
 */
class ContohKeuanganJuniSeeder extends Seeder
{
    /** @var \Illuminate\Support\Collection<string,int> peta kode_akun => id */
    private $akun;

    private Periode $juni;
    private Periode $juli;
    private int $userId;

    /** @var array<string,int> peta deskripsi jurnal umum Juni => id (untuk referensi koreksi) */
    private array $refJurnalJuni = [];

    public function run(): void
    {
        // Idempoten sederhana: jangan gandakan bila sudah ada jurnal pembuka Juni.
        if (Jurnal::where('jenis_jurnal', 'PEMBUKA')
            ->whereDate('tanggal', '2026-06-01')->exists()) {
            $this->command?->warn('Contoh data Juni sudah ada. Seeder dilewati.');
            return;
        }

        $this->akun = Akun::pluck('id', 'kode_akun');
        if ($this->akun->isEmpty()) {
            $this->command?->error('Tabel akun kosong. Jalankan AkunSeeder terlebih dahulu.');
            return;
        }

        $this->userId = User::orderBy('id')->value('id') ?? 1;

        DB::transaction(function () {
            $this->siapkanPeriode();
            $this->siapkanDompet();
            $this->buatAset();

            // --- JUNI ---
            $this->jurnalPembuka();
            $this->jurnalUmumJuni();
            $this->jurnalPenyesuaian();
            $this->jurnalPenutup();
            $this->tutupJuniAktifkanJuli();

            // --- JULI (periode aktif) ---
            $this->jurnalUmumJuli();
            $this->jurnalKoreksiJuli();
        });

        $this->command?->info('Selesai: Juni 2026 (tutup) + Juli 2026 (aktif, ada koreksi) dibuat.');
    }

    // ---------------------------------------------------------------
    // 0. Periode: Juni (akan ditutup) & Juli (aktif)
    // ---------------------------------------------------------------
    private function siapkanPeriode(): void
    {
        $this->juni = Periode::firstOrCreate(
            ['tanggal_awal' => '2026-06-01', 'tanggal_akhir' => '2026-06-30'],
            ['nama_periode' => 'Juni 2026', 'tipe' => 'bulanan', 'status' => true]
        );

        $this->juli = Periode::firstOrCreate(
            ['tanggal_awal' => '2026-07-01', 'tanggal_akhir' => '2026-07-31'],
            ['nama_periode' => 'Juli 2026', 'tipe' => 'bulanan', 'status' => false]
        );

        // Juni aktif dulu selama proses pencatatan; ditutup di akhir tahap Juni.
        $this->juni->update(['status' => true]);
    }

    // ---------------------------------------------------------------
    // 0b. Dompet: Tunai (Kas Kecil), BRI, BSI
    // ---------------------------------------------------------------
    private function siapkanDompet(): void
    {
        Dompet::firstOrCreate(
            ['nama_dompet' => 'Tunai (Kas Kecil)'],
            ['jenis_dompet' => 'CASH', 'saldo_awal' => 2000000]
        );
        Dompet::firstOrCreate(
            ['nama_dompet' => 'Bank BRI'],
            ['jenis_dompet' => 'BANK', 'nomor_rekening' => '0123456789', 'nama_bank' => 'BRI', 'saldo_awal' => 5000000]
        );
        Dompet::firstOrCreate(
            ['nama_dompet' => 'Bank BSI'],
            ['jenis_dompet' => 'BANK', 'nomor_rekening' => '7123456789', 'nama_bank' => 'BSI', 'saldo_awal' => 3000000]
        );
    }

    private function dompet(string $nama): int
    {
        return Dompet::where('nama_dompet', $nama)->value('id');
    }

    private function kategori(string $nama): ?int
    {
        return KategoriTransaksi::where('nama_kategori', $nama)->value('id');
    }

    // ---------------------------------------------------------------
    // 1. Aset (sedikit, nominal kecil): 1 tak-disusutkan + 2 disusutkan.
    //    Akumulasi & nilai buku SUDAH mencerminkan penyusutan Juni
    //    (karena periode Juni ditutup).
    // ---------------------------------------------------------------
    private function buatAset(): void
    {
        $data = [
            [
                'kode_aset'                => 'ASET-2015-001',
                'nama_aset'                => 'Tanah Wakaf Masjid',
                'sumber_perolehan'         => 'Wakaf',
                'tanggal_perolehan'        => '2015-01-10',
                'nilai_tercatat'           => 20000000,
                'umur_manfaat'             => null,          // TANAH tidak disusutkan
                'tanggal_mulai_penyusutan' => null,
                'kondisi_aset'             => 'BAIK',
                'lokasi_aset'              => 'Kompleks Masjid Lukmanul Hakim',
                'nama_pemberi'             => 'H. Abdullah',
                'jumlah_unit'              => 1,
                'status_aset'              => 'AKTIF',
                'akumulasi_penyusutan'     => null,
                'nilai_buku'               => 20000000,
            ],
            [
                'kode_aset'                => 'ASET-2015-002',
                'nama_aset'                => 'Bangunan Masjid',
                'sumber_perolehan'         => 'Wakaf',
                'tanggal_perolehan'        => '2015-01-10',
                'nilai_tercatat'           => 30000000,
                'umur_manfaat'             => 25,            // 25 th -> 100.000/bln
                'tanggal_mulai_penyusutan' => '2015-02-01',
                'kondisi_aset'             => 'BAIK',
                'lokasi_aset'              => 'Kompleks Masjid Lukmanul Hakim',
                'nama_pemberi'             => 'Panitia Pembangunan',
                'jumlah_unit'              => 1,
                'status_aset'              => 'AKTIF',
                // Konsisten dgn tgl mulai penyusutan 2015-02-01 (100.000/bln):
                // s/d 31 Mei 2026 = 136 bln x 100.000 = 13.600.000, + Juni 100.000 = 13.700.000.
                // Umur 300 bln, baru terpakai 137 bln -> BELUM habis, masih disusutkan.
                'akumulasi_penyusutan'     => 13700000,
                'nilai_buku'               => 16300000,
            ],
            [
                'kode_aset'                => 'ASET-2022-001',
                'nama_aset'                => 'Sound System & Amplifier',
                'sumber_perolehan'         => 'Pembelian',
                'tanggal_perolehan'        => '2022-01-15',
                'nilai_tercatat'           => 12000000,
                'umur_manfaat'             => 5,             // 5 th -> 200.000/bln
                'tanggal_mulai_penyusutan' => '2022-02-01',
                'kondisi_aset'             => 'BAIK',
                'lokasi_aset'              => 'Ruang Utama Masjid',
                'nama_pemberi'             => null,
                'jumlah_unit'              => 1,
                'status_aset'              => 'AKTIF',
                // Konsisten dgn tgl mulai penyusutan 2022-02-01 (200.000/bln):
                // s/d 31 Mei 2026 = 52 bln x 200.000 = 10.400.000, + Juni 200.000 = 10.600.000.
                // Umur 60 bln, baru terpakai 53 bln -> BELUM habis (nilai buku 1.400.000).
                'akumulasi_penyusutan'     => 10600000,
                'nilai_buku'               => 1400000,
            ],
        ];

        foreach ($data as $d) {
            Aset::create($d);
        }
    }

    private function aset(string $nama): Aset
    {
        return Aset::where('nama_aset', $nama)->firstOrFail();
    }

    // ---------------------------------------------------------------
    // Helper pembuat jurnal + detail (dengan pengecekan seimbang).
    // $detail = array of [kode_akun, 'DEBIT'|'KREDIT', nominal]
    // ---------------------------------------------------------------
    private function buatJurnal(array $atribut, array $detail): Jurnal
    {
        $totalD = 0;
        $totalK = 0;
        foreach ($detail as [$kode, $tipe, $nom]) {
            $tipe === 'DEBIT' ? $totalD += $nom : $totalK += $nom;
        }
        if (round($totalD, 2) !== round($totalK, 2)) {
            throw new \RuntimeException(
                "Jurnal tidak seimbang ({$atribut['keterangan']}): D={$totalD} K={$totalK}"
            );
        }

        $jurnal = Jurnal::create($atribut);
        foreach ($detail as [$kode, $tipe, $nom]) {
            $akunId = $this->akun[$kode] ?? null;
            if (!$akunId) {
                throw new \RuntimeException("Akun {$kode} tidak ditemukan di CoA.");
            }
            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id'   => $akunId,
                'tipe'      => $tipe,
                'nominal'   => $nom,
            ]);
        }
        return $jurnal;
    }

    /**
     * Membuat 1 Transaksi + 1 Jurnal Umum yang tertaut. Mengembalikan Jurnal.
     */
    private function buatTransaksiUmum(
        Periode $periode, string $tgl, string $jenis, int $jumlah,
        string $deskripsi, string $namaKat, string $namaDompet, array $detail
    ): Jurnal {
        $transaksi = Transaksi::create([
            'dompet_id'             => $this->dompet($namaDompet),
            'user_id'               => $this->userId,
            // Transaksi umum: kegiatan_id NULL -> kategori_transaksi_id juga NULL.
            // Kategori transaksi hanya dipakai untuk transaksi kegiatan
            // (StoreTransaksiKegiatanRequest mewajibkannya). $namaKat di sini
            // hanya sebagai label deskriptif di definisi data, tidak disimpan.
            'kegiatan_id'           => null,
            'kategori_transaksi_id' => null,
            'tanggal_transaksi'     => $tgl,
            'jenis_transaksi'       => $jenis,
            'jumlah'                => $jumlah,
            'deskripsi'             => $deskripsi,
            'status_persetujuan'       => 'APPROVED',
            'status_jurnal'         => 'MAPPED',
        ]);

        return $this->buatJurnal([
            'periode_id'   => $periode->id,
            'transaksi_id' => $transaksi->id,
            'jenis_jurnal' => 'UMUM',
            'tanggal'      => $tgl,
            'keterangan'   => $deskripsi,
            'status'       => 'POSTED',
        ], $detail);
    }

    // ---------------------------------------------------------------
    // 2. JURNAL PEMBUKA - saldo awal 1 Juni 2026
    // ---------------------------------------------------------------
    private function jurnalPembuka(): void
    {
        $this->buatJurnal([
            'periode_id'   => $this->juni->id,
            'jenis_jurnal' => 'PEMBUKA',
            'tanggal'      => '2026-06-01',
            'keterangan'   => 'Jurnal pembuka - saldo awal per 1 Juni 2026',
            'status'       => 'POSTED',
        ], [
            // ASET (debit)
            ['1-1001', 'DEBIT', 2000000],    // Kas Kecil
            ['1-1002', 'DEBIT', 5000000],    // Kas Infak
            ['1-1003', 'DEBIT', 3000000],    // Kas Zakat (dana terikat)
            ['1-2001', 'DEBIT', 20000000],   // Tanah Masjid
            ['1-2002', 'DEBIT', 30000000],   // Bangunan Masjid
            ['1-2006', 'DEBIT', 12000000],   // Peralatan Masjid (Sound System)
            // KONTRA-ASET (kredit) - akumulasi penyusutan s/d 31 Mei 2026
            ['1-2003', 'KREDIT', 13600000],  // Akum. Penyusutan Bangunan (136 bln x 100.000)
            ['1-2007', 'KREDIT', 10400000],  // Akum. Penyusutan Peralatan (52 bln x 200.000)
            // ASET NETO (kredit)
            ['3-2101', 'KREDIT', 3000000],   // Dana Zakat Maal (terikat) = saldo Kas Zakat
            ['3-1002', 'KREDIT', 45000000],  // Saldo Awal Aset Neto (tanpa pembatasan) - penyeimbang
        ]);
    }

    // ---------------------------------------------------------------
    // 3. JURNAL UMUM JUNI - transaksi operasional (via Transaksi -> Jurnal)
    // ---------------------------------------------------------------
    private function jurnalUmumJuni(): void
    {
        // [tanggal, jenis, jumlah, deskripsi, kategori, dompet, [detail jurnal]]
        $trx = [
            ['2026-06-05', 'PEMASUKAN', 500000, 'Infak tunai jamaah', 'Infak Harian', 'Tunai (Kas Kecil)', [
                ['1-1001', 'DEBIT', 500000], ['4-1001', 'KREDIT', 500000],
            ]],
            ['2026-06-07', 'PEMASUKAN', 1000000, 'Infak kotak amal Jumat', 'Infak Jumat', 'Bank BRI', [
                ['1-1002', 'DEBIT', 1000000], ['4-1002', 'KREDIT', 1000000],
            ]],
            ['2026-06-10', 'PEMASUKAN', 1500000, 'Penerimaan zakat maal (uang)', 'Penerimaan Zakat Maal', 'Bank BSI', [
                ['1-1003', 'DEBIT', 1500000], ['4-2102', 'KREDIT', 1500000],
            ]],
            ['2026-06-12', 'PEMASUKAN', 2000000, 'Donasi pembangunan masjid', 'Penerimaan Dana Pembangunan', 'Bank BSI', [
                ['1-1002', 'DEBIT', 2000000], ['4-2401', 'KREDIT', 2000000],
            ]],
            // Penyaluran zakat (dana terikat) ke asnaf - dibayar dari Kas Zakat.
            ['2026-06-22', 'PENGELUARAN', 1000000, 'Penyaluran zakat maal untuk fakir', 'Penyaluran Zakat', 'Bank BSI', [
                ['5-2101', 'DEBIT', 1000000], ['1-1003', 'KREDIT', 1000000],
            ]],
            ['2026-06-23', 'PENGELUARAN', 500000, 'Penyaluran zakat maal untuk miskin', 'Penyaluran Zakat', 'Bank BSI', [
                ['5-2102', 'DEBIT', 500000], ['1-1003', 'KREDIT', 500000],
            ]],
            ['2026-06-15', 'PENGELUARAN', 300000, 'Pembayaran listrik masjid', 'Operasional Masjid', 'Tunai (Kas Kecil)', [
                ['5-1101', 'DEBIT', 300000], ['1-1001', 'KREDIT', 300000],
            ]],
            ['2026-06-15', 'PENGELUARAN', 100000, 'Pembayaran air (PDAM)', 'Operasional Masjid', 'Tunai (Kas Kecil)', [
                ['5-1102', 'DEBIT', 100000], ['1-1001', 'KREDIT', 100000],
            ]],
            ['2026-06-20', 'PENGELUARAN', 150000, 'Jasa kebersihan masjid', 'Operasional Masjid', 'Tunai (Kas Kecil)', [
                ['5-1104', 'DEBIT', 150000], ['1-1001', 'KREDIT', 150000],
            ]],
            ['2026-06-25', 'PENGELUARAN', 500000, 'Honor imam & muadzin', 'Honorarium', 'Tunai (Kas Kecil)', [
                ['5-1106', 'DEBIT', 500000], ['1-1001', 'KREDIT', 500000],
            ]],
            ['2026-06-28', 'PENGELUARAN', 1000000, 'Pembayaran material pembangunan', 'Beban Pembangunan', 'Bank BSI', [
                ['5-2401', 'DEBIT', 1000000], ['1-1002', 'KREDIT', 1000000],
            ]],
        ];

        foreach ($trx as [$tgl, $jenis, $jumlah, $deskripsi, $namaKat, $namaDompet, $detail]) {
            $jurnal = $this->buatTransaksiUmum(
                $this->juni, $tgl, $jenis, $jumlah, $deskripsi, $namaKat, $namaDompet, $detail
            );
            // simpan id untuk referensi koreksi
            $this->refJurnalJuni[$deskripsi] = $jurnal->id;
        }
    }

    // ---------------------------------------------------------------
    // 4. JURNAL PENYESUAIAN - penyusutan aset (30 Juni)
    //    Setiap aset yang disusutkan DILAMPIRKAN (jurnal_aset) agar syarat
    //    penutupan terpenuhi.
    // ---------------------------------------------------------------
    private function jurnalPenyesuaian(): void
    {
        $jurnal = $this->buatJurnal([
            'periode_id'       => $this->juni->id,
            'jenis_jurnal'     => 'PENYESUAIAN',
            'tipe_penyesuaian' => 'PENYUSUTAN_ASET',
            'tanggal'          => '2026-06-30',
            'keterangan'       => 'Penyusutan aset tetap - Juni 2026',
            'status'           => 'POSTED',
        ], [
            ['5-1401', 'DEBIT', 100000],   // Beban Penyusutan Bangunan
            ['5-1402', 'DEBIT', 200000],   // Beban Penyusutan Peralatan Masjid
            ['1-2003', 'KREDIT', 100000],  // Akum. Penyusutan Bangunan
            ['1-2007', 'KREDIT', 200000],  // Akum. Penyusutan Peralatan Masjid
        ]);

        // Lampirkan aset + nominal penyusutan bulan ini (pivot jurnal_aset).
        $jurnal->lampirkanAset($this->aset('Bangunan Masjid')->id, 100000);
        $jurnal->lampirkanAset($this->aset('Sound System & Amplifier')->id, 200000);
    }

    // ---------------------------------------------------------------
    // 5. JURNAL PENUTUP - 30 Juni (mengikuti logika JurnalPenutupService)
    // ---------------------------------------------------------------
    private function jurnalPenutup(): void
    {
        // a) Tutup Pendapatan: debit tiap pendapatan; kredit akun dana tujuan.
        $this->buatJurnal([
            'periode_id'     => $this->juni->id,
            'jenis_jurnal'   => 'PENUTUP',
            'tipe_penutupan' => 'TUTUP_PENDAPATAN',
            'tanggal'        => '2026-06-30',
            'keterangan'     => 'Tutup Pendapatan - Juni 2026',
            'status'         => 'POSTED',
        ], [
            ['4-1001', 'DEBIT', 500000],
            ['4-1002', 'DEBIT', 1000000],
            ['4-2102', 'DEBIT', 1500000],
            ['4-2401', 'DEBIT', 2000000],
            ['3-1001', 'KREDIT', 1500000],  // Dana Umum (4-1001 + 4-1002)
            ['3-2101', 'KREDIT', 1500000],  // Dana Zakat Maal (4-2102)
            ['3-2401', 'KREDIT', 2000000],  // Dana Pembangunan (4-2401)
        ]);

        // b) Tutup Beban: debit Dana Umum (3-1001) sebesar total beban; kredit tiap beban.
        $this->buatJurnal([
            'periode_id'     => $this->juni->id,
            'jenis_jurnal'   => 'PENUTUP',
            'tipe_penutupan' => 'TUTUP_BEBAN',
            'tanggal'        => '2026-06-30',
            'keterangan'     => 'Tutup Beban - Juni 2026',
            'status'         => 'POSTED',
        ], [
            ['3-1001', 'DEBIT', 3850000],   // total seluruh beban
            ['5-1101', 'KREDIT', 300000],
            ['5-1102', 'KREDIT', 100000],
            ['5-1104', 'KREDIT', 150000],
            ['5-1106', 'KREDIT', 500000],
            ['5-1401', 'KREDIT', 100000],
            ['5-1402', 'KREDIT', 200000],
            ['5-2101', 'KREDIT', 1000000],  // penyaluran zakat fakir (dana terikat zakat)
            ['5-2102', 'KREDIT', 500000],   // penyaluran zakat miskin (dana terikat zakat)
            ['5-2401', 'KREDIT', 1000000],  // beban dana terikat (pembangunan)
        ]);

        // c) Pelepasan Pembatasan: dikelompokkan PER DANA terikat yang terpakai
        //    (mengikuti susunJurnalPelepasanPembatasan):
        //      - Dana Zakat Maal (F=1): 5-2101 + 5-2102 = 1.500.000 -> lepas dari 3-2101
        //      - Dana Pembangunan (F=4): 5-2401 = 1.000.000        -> lepas dari 3-2401
        $this->buatJurnal([
            'periode_id'     => $this->juni->id,
            'jenis_jurnal'   => 'PENUTUP',
            'tipe_penutupan' => 'PELEPASAN_PEMBATASAN',
            'tanggal'        => '2026-06-30',
            'keterangan'     => 'Pelepasan Aset Neto dari Pembatasan - Juni 2026',
            'status'         => 'POSTED',
        ], [
            ['3-2101', 'DEBIT', 1500000],   // Dana Zakat Maal (dilepas, terpakai untuk penyaluran)
            ['3-2401', 'DEBIT', 1000000],   // Dana Pembangunan (dilepas)
            ['3-1001', 'KREDIT', 2500000],  // ke Dana Umum
        ]);
    }

    // ---------------------------------------------------------------
    // 6. Tutup periode Juni, aktifkan Juli
    // ---------------------------------------------------------------
    private function tutupJuniAktifkanJuli(): void
    {
        Periode::query()->update(['status' => false]); // non-aktifkan semua
        $this->juli->refresh()->update(['status' => true]);
        $this->juni->refresh()->update(['status' => false]);
    }

    // ---------------------------------------------------------------
    // 7. JURNAL UMUM JULI - 3 transaksi baru di periode aktif
    // ---------------------------------------------------------------
    private function jurnalUmumJuli(): void
    {
        $trx = [
            ['2026-07-03', 'PEMASUKAN', 600000, 'Infak tunai jamaah (Juli)', 'Infak Harian', 'Tunai (Kas Kecil)', [
                ['1-1001', 'DEBIT', 600000], ['4-1001', 'KREDIT', 600000],
            ]],
            ['2026-07-04', 'PEMASUKAN', 1200000, 'Infak kotak amal Jumat (Juli)', 'Infak Jumat', 'Bank BRI', [
                ['1-1002', 'DEBIT', 1200000], ['4-1002', 'KREDIT', 1200000],
            ]],
            ['2026-07-10', 'PENGELUARAN', 350000, 'Pembayaran listrik masjid (Juli)', 'Operasional Masjid', 'Tunai (Kas Kecil)', [
                ['5-1101', 'DEBIT', 350000], ['1-1001', 'KREDIT', 350000],
            ]],
        ];

        foreach ($trx as [$tgl, $jenis, $jumlah, $deskripsi, $namaKat, $namaDompet, $detail]) {
            $this->buatTransaksiUmum(
                $this->juli, $tgl, $jenis, $jumlah, $deskripsi, $namaKat, $namaDompet, $detail
            );
        }
    }

    // ---------------------------------------------------------------
    // 8. JURNAL KOREKSI (Juli) - memperbaiki jurnal Juni yang sudah tutup
    //
    // Skenario: infak tunai Juni ternyata KURANG DICATAT Rp 50.000
    // (uang fisik ada, tapi belum dibukukan). Karena periode Juni sudah
    // tutup & pendapatannya sudah ditutup ke aset neto, koreksi dicatat di
    // periode aktif (Juli) sebagai penyesuaian periode lalu:
    //   - DEBIT  Kas Kecil (1-1001)             -> menambah kas yang belum tercatat
    //   - KREDIT Aset Neto Tanpa Pembatasan (3-1001) -> menambah aset neto
    // jurnal_ref_id menunjuk jurnal Umum "Infak tunai jamaah" (Juni).
    // ---------------------------------------------------------------
    private function jurnalKoreksiJuli(): void
    {
        $refId = $this->refJurnalJuni['Infak tunai jamaah'] ?? null;

        $this->buatJurnal([
            'periode_id'    => $this->juli->id,        // WAJIB periode aktif
            'jurnal_ref_id' => $refId,                 // jurnal Juni yang dikoreksi
            'jenis_jurnal'  => 'KOREKSI',
            'tanggal'       => '2026-07-05',
            'keterangan'    => 'Koreksi periode lalu: infak tunai Juni kurang dicatat Rp50.000',
            'status'        => 'POSTED',
        ], [
            ['1-1001', 'DEBIT', 50000],    // Kas Kecil bertambah
            ['3-1001', 'KREDIT', 50000],   // Aset Neto Tanpa Pembatasan bertambah
        ]);
    }
}
