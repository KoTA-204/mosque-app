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
 * dengan rincian Aset Neto Dengan Pembatasan dipecah per dana (3-201..3-206).
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
        $this->buatJurnalPenutup($periode);

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
            ['kode' => '1-101', 'tipe' => 'DEBIT',  'nominal' => 20000000],   // Kas Kecil
            ['kode' => '1-102', 'tipe' => 'DEBIT',  'nominal' => 15000000],   // Kas Infak
            ['kode' => '1-103', 'tipe' => 'DEBIT',  'nominal' => 25000000],   // Kas Zakat
            ['kode' => '1-104', 'tipe' => 'DEBIT',  'nominal' => 3000000],    // Piutang
            ['kode' => '1-105', 'tipe' => 'DEBIT',  'nominal' => 2400000],    // Beban Dibayar Dimuka
            ['kode' => '1-106', 'tipe' => 'DEBIT',  'nominal' => 5000000],    // Perlengkapan Masjid
            // ASET TETAP (debit)
            ['kode' => '1-201', 'tipe' => 'DEBIT',  'nominal' => 500000000],  // Tanah Masjid
            ['kode' => '1-202', 'tipe' => 'DEBIT',  'nominal' => 800000000],  // Bangunan Masjid
            ['kode' => '1-204', 'tipe' => 'DEBIT',  'nominal' => 50000000],   // Aset Dalam Pembangunan
            ['kode' => '1-205', 'tipe' => 'DEBIT',  'nominal' => 40000000],   // Investasi Jangka Panjang
            ['kode' => '1-206', 'tipe' => 'DEBIT',  'nominal' => 100000000],  // Peralatan Masjid
            // KONTRA-ASET (kredit)
            ['kode' => '1-203', 'tipe' => 'KREDIT', 'nominal' => 80000000],   // Akum. Penyusutan Bangunan
            ['kode' => '1-207', 'tipe' => 'KREDIT', 'nominal' => 30000000],   // Akum. Penyusutan Peralatan Masjid
            // LIABILITAS (kredit)
            ['kode' => '2-101', 'tipe' => 'KREDIT', 'nominal' => 1000000],    // Utang Operasional
            ['kode' => '2-102', 'tipe' => 'KREDIT', 'nominal' => 900000],     // Utang Listrik
            ['kode' => '2-103', 'tipe' => 'KREDIT', 'nominal' => 500000],     // Utang Air
            ['kode' => '2-104', 'tipe' => 'KREDIT', 'nominal' => 2000000],    // Utang Honorarium
            ['kode' => '2-105', 'tipe' => 'KREDIT', 'nominal' => 1500000],    // Utang Kegiatan
            ['kode' => '2-106', 'tipe' => 'KREDIT', 'nominal' => 5000000],    // Dana Titipan Zakat Maal
            ['kode' => '2-107', 'tipe' => 'KREDIT', 'nominal' => 3000000],    // Dana Titipan Zakat Fitrah
            ['kode' => '2-108', 'tipe' => 'KREDIT', 'nominal' => 4000000],    // Dana Titipan Qurban
            ['kode' => '2-201', 'tipe' => 'KREDIT', 'nominal' => 20000000],   // Utang Jangka Panjang
            // ASET NETO DENGAN PEMBATASAN (kredit)
            ['kode' => '3-201', 'tipe' => 'KREDIT', 'nominal' => 30000000],   // Dana Zakat Maal
            ['kode' => '3-202', 'tipe' => 'KREDIT', 'nominal' => 10000000],   // Dana Zakat Fitrah
            ['kode' => '3-203', 'tipe' => 'KREDIT', 'nominal' => 40000000],   // Dana Wakaf
            ['kode' => '3-204', 'tipe' => 'KREDIT', 'nominal' => 60000000],   // Dana Pembangunan
            ['kode' => '3-205', 'tipe' => 'KREDIT', 'nominal' => 8000000],    // Dana Qurban
            ['kode' => '3-206', 'tipe' => 'KREDIT', 'nominal' => 12000000],   // Dana Program Terikat
            // ASET NETO TANPA PEMBATASAN — penyeimbang (kredit)
            ['kode' => '3-101', 'tipe' => 'KREDIT', 'nominal' => 1252500000], // Saldo Awal Aset Neto (penyeimbang)
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
            ['kode' => '5-601', 'tipe' => 'DEBIT',  'nominal' => 5000000], // Penyusutan Bangunan
            ['kode' => '5-602', 'tipe' => 'DEBIT',  'nominal' => 2000000], // Penyusutan Peralatan Masjid
            ['kode' => '1-203', 'tipe' => 'KREDIT', 'nominal' => 5000000], // Akum. Penyusutan Bangunan
            ['kode' => '1-207', 'tipe' => 'KREDIT', 'nominal' => 2000000], // Akum. Penyusutan Peralatan Masjid
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
            ['kode' => '5-101', 'tipe' => 'DEBIT',  'nominal' => 900000], // Listrik
            ['kode' => '2-102', 'tipe' => 'KREDIT', 'nominal' => 900000], // Utang Listrik
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
            ['kode' => '5-105', 'tipe' => 'DEBIT',  'nominal' => 250000], // Perlengkapan Masjid
            ['kode' => '5-104', 'tipe' => 'KREDIT', 'nominal' => 250000], // Kebersihan
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
            '4-101' => 8000000, '4-102' => 6000000, '4-103' => 3500000,
            '4-104' => 5000000, '4-105' => 2000000, '4-106' => 1500000,
            // Pendapatan dengan pembatasan
            '4-201' => 5000000, '4-202' => 8000000, '4-203' => 6000000, '4-204' => 3000000,
            '4-205' => 10000000, '4-206' => 2500000, '4-207' => 4000000,
            '4-208' => 7000000, '4-209' => 5000000,
            '4-210' => 20000000, '4-211' => 15000000,
            '4-212' => 25000000, '4-213' => 12000000, '4-214' => 6000000,
            // Beban operasional
            '5-101' => 1500000, '5-102' => 1200000, '5-103' => 900000, '5-104' => 2000000,
            '5-105' => 1500000, '5-106' => 6000000, '5-107' => 3000000, '5-108' => 2500000,
            // Beban kegiatan
            '5-201' => 4000000, '5-202' => 3000000, '5-203' => 5000000, '5-204' => 3500000, '5-205' => 4000000,
            // Beban penyaluran zakat (per asnaf + fitrah)
            '5-301' => 5000000, '5-302' => 5000000, '5-303' => 2000000, '5-304' => 1500000,
            '5-305' => 1000000, '5-306' => 1500000, '5-307' => 1000000, '5-308' => 3000000,
            // Beban lainnya
            '5-401' => 4000000, '5-402' => 8000000, '5-403' => 5000000, '5-404' => 2000000, '5-405' => 1000000,
            // Beban pemeliharaan
            '5-501' => 3000000, '5-502' => 2000000, '5-503' => 1500000,
            // Beban penyusutan
            '5-601' => 5000000, '5-602' => 2000000,
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
                    ['kode' => '1-101',          'tipe' => 'DEBIT',  'nominal' => $nominal], // Kas Kecil
                    ['kode' => $akun->kode_akun, 'tipe' => 'KREDIT', 'nominal' => $nominal],
                ]
                : [
                    ['kode' => $akun->kode_akun, 'tipe' => 'DEBIT',  'nominal' => $nominal],
                    ['kode' => '1-101',          'tipe' => 'KREDIT', 'nominal' => $nominal], // Kas Kecil
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
        // Pendapatan tanpa pembatasan -> Surplus/Defisit Tahun Berjalan
        if (str_starts_with($kode, '4-1')) return '3-102';

        // Pendapatan dengan pembatasan -> dana terikat sesuai jenis
        if (in_array($kode, ['4-201','4-202','4-203','4-204','4-205','4-206','4-207'], true)) return '3-201'; // Zakat Maal
        if (in_array($kode, ['4-208','4-209'], true)) return '3-202'; // Zakat Fitrah
        if (in_array($kode, ['4-210','4-211'], true)) return '3-203'; // Wakaf
        if ($kode === '4-212') return '3-204'; // Dana Pembangunan
        if ($kode === '4-213') return '3-205'; // Dana Qurban
        if ($kode === '4-214') return '3-206'; // Donasi Terikat Program

        // Beban yang mengurangi dana terikat
        if (in_array($kode, ['5-301','5-302','5-303','5-304','5-305','5-306','5-307'], true)) return '3-201'; // Penyaluran Zakat Maal
        if ($kode === '5-308') return '3-202'; // Penyaluran Zakat Fitrah
        if ($kode === '5-401') return '3-203'; // Penyaluran Wakaf
        if ($kode === '5-402') return '3-204'; // Beban Pembangunan Masjid
        if ($kode === '5-403') return '3-205'; // Beban Qurban
        if ($kode === '5-404') return '3-206'; // Bantuan Sosial (dana program terikat)

        // Beban lain -> Surplus/Defisit Tahun Berjalan (tanpa pembatasan)
        return '3-102';
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
