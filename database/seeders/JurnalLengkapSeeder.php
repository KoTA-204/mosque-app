<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder jurnal lengkap: PEMBUKA → PENYESUAIAN → KOREKSI → KELENGKAPAN → PENUTUP.
 *
 * Menggunakan Chart of Accounts sesuai Laporan Tugas Akhir (Tabel IV.7),
 * dengan rincian Aset Neto Dengan Pembatasan dipecah per dana (3-2101..3-2601).
 *
 * Dijalankan SETELAH AkunSeeder, PeriodeSeeder, dan JurnalUmumSeeder.
 */
class JurnalLengkapSeeder extends Seeder
{
    private $akun; // peta kode_akun => id

    public function run(): void
    {
        $this->akun = Akun::pluck('id', 'kode_akun');

        // Anchor semua jurnal ke periode AKTIF agar laporan yang default
        // menampilkan periode aktif langsung berisi angka.
        $periode = Periode::where('status', true)->orderBy('id')->first()
            ?? Periode::where('tipe', 'bulanan')->orderByDesc('tanggal_awal')->first()
            ?? Periode::orderByDesc('tanggal_awal')->first();

        if (!$periode) {
            $this->command->warn('⚠️  JurnalLengkapSeeder dilewati — periode belum tersedia.');
            return;
        }

        $this->buatJurnalPembuka($periode);
        $this->buatJurnalPenyesuaian($periode);
        $this->buatJurnalKoreksi($periode);
        $this->buatJurnalKelengkapan($periode);
        // $this->buatJurnalPenutup($periode);

        $this->command->info('✅ JurnalLengkapSeeder selesai — jurnal pembuka, penyesuaian, koreksi, kelengkapan & penutup dibuat.');
    }

    // ── Helper pembuat jurnal + detail ────────────────────────
    private function buatJurnal(array $atribut, array $detail): ?Jurnal
    {
        // Bersihkan baris yang akunnya tidak ditemukan / nominal <= 0
        $bersih = [];
        foreach ($detail as $row) {
            $akunId = $this->akun[$row['kode']] ?? null;
            if (!$akunId || $row['nominal'] <= 0) {
                continue;
            }
            $bersih[] = [
                'akun_id' => $akunId,
                'tipe'    => $row['tipe'],
                'nominal' => $row['nominal'],
            ];
        }

        // Pastikan seimbang
        $totalDebit  = array_sum(array_map(fn($r) => $r['tipe'] === 'DEBIT'  ? $r['nominal'] : 0, $bersih));
        $totalKredit = array_sum(array_map(fn($r) => $r['tipe'] === 'KREDIT' ? $r['nominal'] : 0, $bersih));

        if (empty($bersih) || round($totalDebit, 2) !== round($totalKredit, 2)) {
            return null;
        }

        $jurnal = Jurnal::create($atribut);
        foreach ($bersih as $row) {
            DetailJurnal::create(array_merge(['jurnal_id' => $jurnal->id], $row));
        }
        return $jurnal;
    }

    // ══ 1. JURNAL PEMBUKA — saldo awal ════════════════════════════
    private function buatJurnalPembuka(Periode $periode): void
    {
        if (Jurnal::where('jenis_jurnal', 'PEMBUKA')->exists()) {
            return;
        }

        $detail = [
            // ASET LANCAR (debit)
            ['kode' => '1-1001', 'tipe' => 'DEBIT',  'nominal' => 20000000],   // Kas Kecil
            ['kode' => '1-1002', 'tipe' => 'DEBIT',  'nominal' => 15000000],   // Kas Infak
            ['kode' => '1-1003', 'tipe' => 'DEBIT',  'nominal' => 25000000],   // Kas Zakat
            ['kode' => '1-1004', 'tipe' => 'DEBIT',  'nominal' => 3000000],    // Piutang
            ['kode' => '1-1005', 'tipe' => 'DEBIT',  'nominal' => 2400000],    // Beban Dibayar Dimuka
            ['kode' => '1-1006', 'tipe' => 'DEBIT',  'nominal' => 5000000],    // Perlengkapan Masjid
            // ASET TETAP (debit)
            ['kode' => '1-2001', 'tipe' => 'DEBIT',  'nominal' => 500000000],  // Tanah Masjid
            ['kode' => '1-2002', 'tipe' => 'DEBIT',  'nominal' => 800000000],  // Bangunan Masjid
            ['kode' => '1-2004', 'tipe' => 'DEBIT',  'nominal' => 50000000],   // Aset Dalam Pembangunan
            ['kode' => '1-2005', 'tipe' => 'DEBIT',  'nominal' => 40000000],   // Investasi Jangka Panjang
            ['kode' => '1-2006', 'tipe' => 'DEBIT',  'nominal' => 100000000],  // Peralatan Masjid
            // KONTRA-ASET (kredit)
            ['kode' => '1-2003', 'tipe' => 'KREDIT', 'nominal' => 80000000],   // Akum. Penyusutan Bangunan
            ['kode' => '1-2007', 'tipe' => 'KREDIT', 'nominal' => 30000000],   // Akum. Penyusutan Peralatan Masjid
            // LIABILITAS (kredit)
            ['kode' => '2-1001', 'tipe' => 'KREDIT', 'nominal' => 1000000],    // Utang Operasional
            ['kode' => '2-1002', 'tipe' => 'KREDIT', 'nominal' => 900000],     // Utang Listrik
            ['kode' => '2-1003', 'tipe' => 'KREDIT', 'nominal' => 500000],     // Utang Air
            ['kode' => '2-1004', 'tipe' => 'KREDIT', 'nominal' => 2000000],    // Utang Honorarium
            ['kode' => '2-1005', 'tipe' => 'KREDIT', 'nominal' => 1500000],    // Utang Kegiatan
            ['kode' => '2-1006', 'tipe' => 'KREDIT', 'nominal' => 5000000],    // Dana Titipan Zakat Maal
            ['kode' => '2-1007', 'tipe' => 'KREDIT', 'nominal' => 3000000],    // Dana Titipan Zakat Fitrah
            ['kode' => '2-1008', 'tipe' => 'KREDIT', 'nominal' => 4000000],    // Dana Titipan Qurban
            ['kode' => '2-2001', 'tipe' => 'KREDIT', 'nominal' => 20000000],   // Utang Jangka Panjang
            // ASET NETO DENGAN PEMBATASAN (kredit)
            ['kode' => '3-2101', 'tipe' => 'KREDIT', 'nominal' => 30000000],   // Dana Zakat Maal
            ['kode' => '3-2201', 'tipe' => 'KREDIT', 'nominal' => 10000000],   // Dana Zakat Fitrah
            ['kode' => '3-2301', 'tipe' => 'KREDIT', 'nominal' => 40000000],   // Dana Wakaf
            ['kode' => '3-2401', 'tipe' => 'KREDIT', 'nominal' => 60000000],   // Dana Pembangunan
            ['kode' => '3-2501', 'tipe' => 'KREDIT', 'nominal' => 8000000],    // Dana Qurban
            ['kode' => '3-2601', 'tipe' => 'KREDIT', 'nominal' => 12000000],   // Dana Program Terikat
            // ASET NETO TANPA PEMBATASAN — penyeimbang (kredit)
            ['kode' => '3-1002', 'tipe' => 'KREDIT', 'nominal' => 1252500000], // Saldo Awal Aset Neto (penyeimbang)
        ];

        $this->buatJurnal([
            'periode_id'   => $periode->id,
            'jenis_jurnal' => 'PEMBUKA',
            'tanggal'      => $periode->tanggal_awal,
            'keterangan'   => 'Jurnal pembuka — saldo awal ' . $periode->nama_periode,
            'status'       => 'POSTED',
        ], $detail);
    }

    // ══ 2. JURNAL PENYESUAIAN ═══════════════════════════
    private function buatJurnalPenyesuaian(Periode $periode): void
    {
        if (Jurnal::where('jenis_jurnal', 'PENYESUAIAN')->exists()) {
            return;
        }

        // a) Penyusutan aset tetap
        $this->buatJurnal([
            'periode_id'       => $periode->id,
            'jenis_jurnal'     => 'PENYESUAIAN',
            'tipe_penyesuaian' => 'PENYUSUTAN_ASET',
            'tanggal'          => $periode->tanggal_akhir,
            'keterangan'       => 'Penyusutan aset tetap ' . $periode->nama_periode,
            'status'           => 'POSTED',
        ], [
            ['kode' => '5-1401', 'tipe' => 'DEBIT',  'nominal' => 5000000], // Penyusutan Bangunan
            ['kode' => '5-1402', 'tipe' => 'DEBIT',  'nominal' => 2000000], // Penyusutan Peralatan Masjid
            ['kode' => '1-2003', 'tipe' => 'KREDIT', 'nominal' => 5000000], // Akum. Penyusutan Bangunan
            ['kode' => '1-2007', 'tipe' => 'KREDIT', 'nominal' => 2000000], // Akum. Penyusutan Peralatan Masjid
        ]);

        // b) Beban yang masih harus dibayar (listrik akhir periode)
        $this->buatJurnal([
            'periode_id'       => $periode->id,
            'jenis_jurnal'     => 'PENYESUAIAN',
            'tipe_penyesuaian' => 'BEBAN_BELUM_DIBAYAR',
            'tanggal'          => $periode->tanggal_akhir,
            'keterangan'       => 'Beban listrik yang masih harus dibayar',
            'status'           => 'POSTED',
        ], [
            ['kode' => '5-1101', 'tipe' => 'DEBIT',  'nominal' => 900000], // Listrik
            ['kode' => '2-1002', 'tipe' => 'KREDIT', 'nominal' => 900000], // Utang Listrik
        ]);
    }

    // ══ 3. JURNAL KOREKSI ═════════════════════════
    private function buatJurnalKoreksi(Periode $periode): void
    {
        if (Jurnal::where('jenis_jurnal', 'KOREKSI')->exists()) {
            return;
        }

        // Reklasifikasi: sebagian beban kebersihan seharusnya beban perlengkapan masjid
        $this->buatJurnal([
            'periode_id'   => $periode->id,
            'jenis_jurnal' => 'KOREKSI',
            'tanggal'      => $periode->tanggal_akhir,
            'keterangan'   => 'Koreksi reklasifikasi beban kebersihan ke beban perlengkapan masjid',
            'status'       => 'POSTED',
        ], [
            ['kode' => '5-1105', 'tipe' => 'DEBIT',  'nominal' => 250000], // Perlengkapan Masjid
            ['kode' => '5-1104', 'tipe' => 'KREDIT', 'nominal' => 250000], // Kebersihan
        ]);
    }

    // ══ 3b. JURNAL KELENGKAPAN — isi semua akun pendapatan & beban leaf ══
    // Memastikan SETIAP akun pendapatan (4-x) & beban (5-x) level terbawah
    // memiliki saldo agar tidak ada baris bernilai 0 di laporan. Akun yang
    // sudah tersentuh transaksi/penyesuaian/koreksi dilewati (tidak digandakan).
    private function buatJurnalKelengkapan(Periode $periode): void
    {
        $nominalDefault = [
            // Pendapatan tanpa pembatasan
            '4-1001' => 8000000, '4-1002' => 6000000, '4-1003' => 3500000,
            '4-1004' => 5000000, '4-1005' => 2000000, '4-1006' => 1500000,
            // Pendapatan dengan pembatasan
            '4-2101' => 5000000, '4-2102' => 8000000, '4-2103' => 6000000, '4-2104' => 3000000,
            '4-2105' => 10000000, '4-2106' => 2500000, '4-2107' => 4000000,
            '4-2201' => 7000000, '4-2202' => 5000000,
            '4-2301' => 20000000, '4-2302' => 15000000,
            '4-2401' => 25000000, '4-2501' => 12000000, '4-2601' => 6000000,
            // Beban operasional
            '5-1101' => 1500000, '5-1102' => 1200000, '5-1103' => 900000, '5-1104' => 2000000,
            '5-1105' => 1500000, '5-1106' => 6000000, '5-1107' => 3000000, '5-1108' => 2500000,
            // Beban kegiatan
            '5-1201' => 4000000, '5-1202' => 3000000, '5-1203' => 5000000, '5-1204' => 3500000, '5-1205' => 4000000,
            // Beban penyaluran zakat (per asnaf + fitrah)
            '5-2101' => 5000000, '5-2102' => 5000000, '5-2103' => 2000000, '5-2104' => 1500000,
            '5-2105' => 1000000, '5-2106' => 1500000, '5-2107' => 1000000, '5-2201' => 3000000,
            // Beban lainnya
            '5-2301' => 4000000, '5-2401' => 8000000, '5-2501' => 5000000, '5-1501' => 2000000, '5-1502' => 1000000,
            // Beban pemeliharaan
            '5-1301' => 3000000, '5-1302' => 2000000, '5-1303' => 1500000,
            // Beban penyusutan
            '5-1401' => 5000000, '5-1402' => 2000000,
        ];
        $fallback = 2000000;

        // Semua akun leaf sejati (tanpa anak) pada 4-x & 5-x
        $leaves = Akun::whereNotNull('parent_id')
            ->whereDoesntHave('children')
            ->where(function ($q) {
                $q->where('kode_akun', 'like', '4-%')
                  ->orWhere('kode_akun', 'like', '5-%');
            })
            ->orderBy('kode_akun')
            ->get();

        foreach ($leaves as $akun) {
            // Lewati jika akun sudah punya saldo di periode ini
            $sudahAda = DetailJurnal::where('akun_id', $akun->id)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periode->id)
                    ->where('status', 'POSTED'))
                ->exists();
            if ($sudahAda) {
                continue;
            }

            $nominal = $nominalDefault[$akun->kode_akun] ?? $fallback;
            $isPendapatan = str_starts_with($akun->kode_akun, '4-');

            $detail = $isPendapatan
                ? [
                    ['kode' => '1-1001',          'tipe' => 'DEBIT',  'nominal' => $nominal], // Kas Kecil
                    ['kode' => $akun->kode_akun, 'tipe' => 'KREDIT', 'nominal' => $nominal],
                ]
                : [
                    ['kode' => $akun->kode_akun, 'tipe' => 'DEBIT',  'nominal' => $nominal],
                    ['kode' => '1-1001',          'tipe' => 'KREDIT', 'nominal' => $nominal], // Kas Kecil
                ];

            $this->buatJurnal([
                'periode_id'   => $periode->id,
                'jenis_jurnal' => 'UMUM',
                'tanggal'      => $periode->tanggal_akhir,
                'keterangan'   => 'Kelengkapan saldo ' . $akun->nama_akun,
                'status'       => 'POSTED',
            ], $detail);
        }
    }

    // Peta akun pendapatan/beban -> akun aset neto (ekuitas) leaf tujuan penutupan.
    private function targetEkuitas(string $kode): string
    {
        // Format kode K-CFUU. Digit-1 setelah tanda "-" = KELAS pembatasan
        // (1=Tanpa Pembatasan, 2=Dengan Pembatasan). Digit-2 = INDEKS dana terikat.
        $segmen = explode('-', $kode)[1] ?? '';
        $kelas  = substr($segmen, 0, 1);
        $indeks = substr($segmen, 1, 1);

        // Tanpa pembatasan -> Surplus/Defisit Tahun Berjalan
        if ($kelas !== '2') {
            return '3-1001';
        }

        // Dengan pembatasan -> akun aset neto dana terikat (3-2<indeks>01)
        return '3-2' . $indeks . '01';
    }

    // ══ 4. JURNAL PENUTUP — tutup pendapatan & beban ═════════════════
    private function buatJurnalPenutup(Periode $periode): void
    {
        if (Jurnal::where('jenis_jurnal', 'PENUTUP')->exists()) {
            return;
        }

        // Hitung saldo pendapatan (4-xxx) & beban (5-xxx) dari seluruh jurnal
        // operasional (UMUM/PENYESUAIAN/KOREKSI) yang sudah POSTED.
        $rows = DetailJurnal::query()
            ->join('jurnal', 'jurnal.id', '=', 'detail_jurnal.jurnal_id')
            ->join('akun', 'akun.id', '=', 'detail_jurnal.akun_id')
            ->whereIn('jurnal.jenis_jurnal', ['UMUM', 'PENYESUAIAN', 'KOREKSI'])
            ->where('jurnal.status', 'POSTED')
            ->where(function ($q) {
                $q->where('akun.kode_akun', 'like', '4-%')
                  ->orWhere('akun.kode_akun', 'like', '5-%');
            })
            ->selectRaw('akun.kode_akun as kode, akun.nama_akun as nama, detail_jurnal.tipe as tipe, SUM(detail_jurnal.nominal) as total')
            ->groupBy('akun.kode_akun', 'akun.nama_akun', 'detail_jurnal.tipe')
            ->get();

        // Rekap saldo per akun
        $pendapatan = []; // kode => saldo (kredit normal)
        $beban      = []; // kode => saldo (debit normal)

        foreach ($rows as $r) {
            $isPendapatan = str_starts_with($r->kode, '4-');
            $nominal = (float) $r->total;

            if ($isPendapatan) {
                $pendapatan[$r->kode] = ($pendapatan[$r->kode] ?? 0)
                    + ($r->tipe === 'KREDIT' ? $nominal : -$nominal);
            } else {
                $beban[$r->kode] = ($beban[$r->kode] ?? 0)
                    + ($r->tipe === 'DEBIT' ? $nominal : -$nominal);
            }
        }

        $pendapatan = array_filter($pendapatan, fn($v) => $v > 0);
        $beban      = array_filter($beban, fn($v) => $v > 0);

        // ── Tutup Pendapatan: Debit tiap pendapatan, Kredit Aset Neto ──────
        $totalPendapatan = array_sum($pendapatan);
        if ($totalPendapatan > 0) {
            $detail = [];
            $kreditEkuitas = []; // kode akun ekuitas (leaf) => total
            foreach ($pendapatan as $kode => $saldo) {
                $detail[] = ['kode' => $kode, 'tipe' => 'DEBIT', 'nominal' => $saldo];
                $tujuan = $this->targetEkuitas($kode);
                $kreditEkuitas[$tujuan] = ($kreditEkuitas[$tujuan] ?? 0) + $saldo;
            }
            foreach ($kreditEkuitas as $kodeEkuitas => $total) {
                $detail[] = ['kode' => $kodeEkuitas, 'tipe' => 'KREDIT', 'nominal' => $total];
            }

            $this->buatJurnal([
                'periode_id'     => $periode->id,
                'jenis_jurnal'   => 'PENUTUP',
                'tipe_penutupan' => 'TUTUP_PENDAPATAN',
                'tanggal'        => $periode->tanggal_akhir,
                'keterangan'     => 'Tutup Pendapatan — ' . $periode->nama_periode,
                'status'         => 'POSTED',
            ], $detail);
        }

        // ── Tutup Beban: Debit Aset Neto, Kredit tiap beban ──────────────
        $totalBeban = array_sum($beban);
        if ($totalBeban > 0) {
            $detail = [];
            $debitEkuitas = []; // kode akun ekuitas (leaf) => total
            foreach ($beban as $kode => $saldo) {
                $detail[] = ['kode' => $kode, 'tipe' => 'KREDIT', 'nominal' => $saldo];
                $tujuan = $this->targetEkuitas($kode);
                $debitEkuitas[$tujuan] = ($debitEkuitas[$tujuan] ?? 0) + $saldo;
            }
            foreach ($debitEkuitas as $kodeEkuitas => $total) {
                $detail[] = ['kode' => $kodeEkuitas, 'tipe' => 'DEBIT', 'nominal' => $total];
            }

            $this->buatJurnal([
                'periode_id'     => $periode->id,
                'jenis_jurnal'   => 'PENUTUP',
                'tipe_penutupan' => 'TUTUP_BEBAN',
                'tanggal'        => $periode->tanggal_akhir,
                'keterangan'     => 'Tutup Beban — ' . $periode->nama_periode,
                'status'         => 'POSTED',
            ], $detail);
        }
    }
}
