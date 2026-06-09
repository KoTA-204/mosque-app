<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JurnalPenutupService extends JurnalService
{
    // ── Kode akun Aset Neto (sesuai AkunSeeder) ───────────────────────────
    const KODE_ASET_NETO_TANPA_PEMBATASAN  = '3-1000';
    const KODE_ASET_NETO_DENGAN_PEMBATASAN = '3-2000';

    const KODE_KATEGORI_PENDAPATAN = '4';
    const KODE_KATEGORI_BEBAN      = '5';

    /**
     * Prefix kode akun pendapatan yang termasuk dana DENGAN PEMBATASAN.
     *
     * Struktur CoA pendapatan (sesuai AkunSeeder):
     *   4-1000  Pendapatan Tidak Terikat  → TANPA PEMBATASAN
     *   4-2000  Pendapatan Terikat        → DENGAN PEMBATASAN ✓
     *     4-2100  Penerimaan Zakat Fitrah
     *     4-2200  Penerimaan Zakat Maal
     *     4-2300  Penerimaan Donasi Qurban
     *     4-2400  Penerimaan Donasi Pembangunan
     *     4-2500  Penerimaan Wakaf Uang
     *
     * Satu prefix '4-2' sudah cukup untuk menangkap semua dana terikat.
     * Jika ada parent dana terikat baru (misal '4-3000'), tambahkan '4-3' di sini.
     */
    const PREFIX_DENGAN_PEMBATASAN = ['4-2'];

    const TIPE_LABELS = [
        'TUTUP_PENDAPATAN' => 'Tutup Pendapatan',
        'TUTUP_BEBAN'      => 'Tutup Beban',
    ];

    // ── Query ──────────────────────────────────────────────────────────────

    public function getList(
        ?string $search    = '',
        ?string $periodeId = '',
        ?string $status    = '',
        int     $perPage   = 10
    ) {
        return Jurnal::with(['periode', 'detailJurnal.akun'])
            ->penutup()
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->when($status,    fn($q) => $q->where('status', strtoupper($status)))
            ->when($search,    fn($q) => $q->where('keterangan', 'like', "%{$search}%"))
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getById(Jurnal $jurnal): Jurnal
    {
        return $jurnal->load('periode', 'detailJurnal.akun');
    }

    // ── Ringkasan saldo periode ────────────────────────────────────────────

    public function getRingkasanPeriode(Periode $periode): array
    {
        $jurnals = Jurnal::with('detailJurnal.akun.kategoriAkun')
            ->where('periode_id', $periode->id)
            ->whereIn('jenis_jurnal', ['UMUM', 'PENYESUAIAN', 'KOREKSI'])
            ->where('status', 'POSTED')
            ->get();

        $pendapatan = collect();
        $beban      = collect();

        foreach ($jurnals as $jurnal) {
            foreach ($jurnal->detailJurnal as $detail) {
                $akun     = $detail->akun;
                $kategori = $akun?->kategoriAkun;
                if (!$kategori) continue;

                $kodeKat = $kategori->kode_kategori;

                if ($kodeKat === self::KODE_KATEGORI_PENDAPATAN) {
                    $this->akumulasiSaldo($pendapatan, $akun, $detail, 'KREDIT');
                }

                if ($kodeKat === self::KODE_KATEGORI_BEBAN) {
                    $this->akumulasiSaldo($beban, $akun, $detail, 'DEBIT');
                }
            }
        }

        $totalPendapatan = $pendapatan->sum('saldo');
        $totalBeban      = $beban->sum('saldo');

        $adaDraftBelumPosting = Jurnal::where('periode_id', $periode->id)
            ->whereIn('jenis_jurnal', ['UMUM','PENYESUAIAN', 'KOREKSI'])
            ->where('status', 'DRAFT')
            ->exists();

        return [
            'pendapatan'            => $pendapatan->values(),
            'beban'                 => $beban->values(),
            'total_pendapatan'      => $totalPendapatan,
            'total_beban'           => $totalBeban,
            'surplus'               => $totalPendapatan - $totalBeban,
            'ada_draft_belum_posting' => $adaDraftBelumPosting,
        ];
    }

    /**
     * Akumulasi saldo akun ke dalam collection berdasarkan saldo normal.
     */
    private function akumulasiSaldo(Collection $collection, $akun, $detail, string $saldoNormal): void
    {
        $key = $akun->id;

        if (!$collection->has($key)) {
            $collection->put($key, ['akun' => $akun, 'saldo' => 0]);
        }

        $item          = $collection->get($key);
        $item['saldo'] += $detail->tipe === $saldoNormal
            ? (float) $detail->nominal
            : -(float) $detail->nominal;

        $collection->put($key, $item);
    }

    // ── Klasifikasi dana ───────────────────────────────────────────────────

    /**
     * Tentukan klasifikasi dana dari kode_akun tanpa kolom tambahan.
     *
     * Contoh:
     *   '4-2100' → str_starts_with('4-2') → DENGAN_PEMBATASAN
     *   '4-1100' → tidak cocok prefix apapun → TANPA_PEMBATASAN
     */
    private function resolveKlasifikasi(Akun $akun): string
    {
        foreach (self::PREFIX_DENGAN_PEMBATASAN as $prefix) {
            if (str_starts_with($akun->kode_akun, $prefix)) {
                return 'DENGAN_PEMBATASAN';
            }
        }

        return 'TANPA_PEMBATASAN';
    }

    private function resolveAkunAsetNetoByKlasifikasi(string $klasifikasi): ?Akun
    {
        $kode = $klasifikasi === 'DENGAN_PEMBATASAN'
            ? self::KODE_ASET_NETO_DENGAN_PEMBATASAN
            : self::KODE_ASET_NETO_TANPA_PEMBATASAN;

        return Akun::where('kode_akun', $kode)->first();
    }

    // ── Status tahap penutupan ─────────────────────────────────────────────

    public function getStatusTahap(Periode $periode): array
    {
        $status = [];

        foreach (array_keys(self::TIPE_LABELS) as $tipe) {
            $jurnal = Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('tipe_penutupan', $tipe)
                ->latest()
                ->first();

            $status[$tipe] = [
                'jurnal'  => $jurnal,
                'selesai' => (bool) $jurnal,
                'ada'     => (bool) $jurnal,
            ];
        }

        return $status;
    }

    public function getTahapSelesai(Periode $periode): int
    {
        $status  = $this->getStatusTahap($periode);
        $selesai = 0;

        foreach (array_keys(self::TIPE_LABELS) as $tipe) {
            if ($status[$tipe]['selesai']) $selesai++;
        }

        return $selesai;
    }

    // ── Generate entri per tahap ───────────────────────────────────────────

    /**
     * Tahap 1 — Tutup Pendapatan.
     *
     * Akun pendapatan dikelompokkan per klasifikasi dana, masing-masing
     * grup ditutup ke Aset Neto yang sesuai — sesuai ISAK 335.
     *
     * Contoh hasil:
     *
     *   [TANPA PEMBATASAN]
     *   Debit  4-1100 Pendapatan Infaq Jumat    2.000.000
     *   Debit  4-1200 Pendapatan Infaq Harian   1.500.000
     *   Debit  4-1300 Pendapatan Kencleng         500.000
     *   Kredit   3-1000 Aset Neto Tanpa Pembatasan   4.000.000
     *
     *   [DENGAN PEMBATASAN]
     *   Debit  4-2100 Penerimaan Zakat Fitrah   1.000.000
     *   Debit  4-2200 Penerimaan Zakat Maal     2.000.000
     *   Kredit   3-2000 Aset Neto Dengan Pembatasan  3.000.000
     */
    public function generateTutupPendapatan(array $ringkasan): array
    {
        if ($ringkasan['total_pendapatan'] <= 0) return [];

        $grouped = collect($ringkasan['pendapatan'])
            ->filter(fn($item) => $item['saldo'] > 0)
            ->groupBy(fn($item) => $this->resolveKlasifikasi($item['akun']));

        $detail = [];

        foreach ($grouped as $klasifikasi => $items) {
            $totalKlasifikasi = $items->sum('saldo');
            $asetNetoAkun     = $this->resolveAkunAsetNetoByKlasifikasi($klasifikasi);

            if (!$asetNetoAkun || $totalKlasifikasi <= 0) continue;

            // Debit setiap akun pendapatan dalam grup ini (nol-kan)
            foreach ($items as $item) {
                $detail[] = $this->buatEntri(
                    $item['akun']->id,
                    $item['akun']->nama_akun,
                    'DEBIT',
                    $item['saldo']
                );
            }

            // Kredit ke Aset Neto sesuai klasifikasi
            $detail[] = $this->buatEntri(
                $asetNetoAkun->id,
                $asetNetoAkun->nama_akun,
                'KREDIT',
                $totalKlasifikasi
            );
        }

        return $detail;
    }

    /**
     * Tahap 2 — Tutup Beban.
     *
     * Sesuai ISAK 335, semua beban masjid dibebankan ke Aset Neto
     * Tanpa Pembatasan. Dana terikat tidak boleh digunakan untuk
     * menutup beban operasional.
     *
     *   Debit  3-1000 Aset Neto Tanpa Pembatasan   5.000.000
     *   Kredit   5-1100 Beban Listrik                   300.000
     *   Kredit   5-1400 Beban Honor Imam                500.000
     *   Kredit   5-2200 Beban Penyaluran Zakat        1.000.000
     *   Kredit   ...
     */
    public function generateTutupBeban(array $ringkasan): array
    {
        if ($ringkasan['total_beban'] <= 0) return [];

        $asetNetoAkun = Akun::where('kode_akun', self::KODE_ASET_NETO_TANPA_PEMBATASAN)->first();

        if (!$asetNetoAkun) return [];

        $detail = [];

        $detail[] = $this->buatEntri(
            $asetNetoAkun->id,
            $asetNetoAkun->nama_akun,
            'DEBIT',
            $ringkasan['total_beban']
        );

        foreach ($ringkasan['beban'] as $item) {
            if ($item['saldo'] <= 0) continue;
            $detail[] = $this->buatEntri(
                $item['akun']->id,
                $item['akun']->nama_akun,
                'KREDIT',
                $item['saldo']
            );
        }

        return $detail;
    }

    /**
     * Helper pembuat array satu baris detail jurnal.
     */
    private function buatEntri(int $akunId, string $namaAkun, string $tipe, float $nominal): array
    {
        return [
            'akun_id' => $akunId,
            'akun'    => $namaAkun,
            'tipe'    => $tipe,
            'nominal' => $nominal,
        ];
    }

    // ── Store per tahap ────────────────────────────────────────────────────

    public function storeAllTahapIfNotExists(Periode $periode, array $semua, string $tanggal, string $status): void
    {
        foreach ($semua as $tipe => $detail) {
            $sudahAda = Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('tipe_penutupan', $tipe)
                ->exists();

            if ($sudahAda) {
                if ($status === 'POSTED') {
                    Jurnal::where('periode_id', $periode->id)
                        ->where('jenis_jurnal', 'PENUTUP')
                        ->where('tipe_penutupan', $tipe)
                        ->update(['status' => 'POSTED']);
                }
            } else {
                $this->storeTahap($periode, $tipe, $detail, $tanggal, $status);
            }
        }
    }

    public function storeTahap(
        Periode $periode,
        string  $tipePenutupan,
        array   $detail,
        string  $tanggal,
        string  $status = 'DRAFT'
    ): Jurnal {
        return DB::transaction(function () use ($periode, $tipePenutupan, $detail, $tanggal, $status) {
            Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('tipe_penutupan', $tipePenutupan)
                ->where('status', 'DRAFT')
                ->each(function ($j) {
                    $j->detailJurnal()->delete();
                    $j->delete();
                });

            $jurnal = Jurnal::create([
                'periode_id'       => $periode->id,
                'transaksi_id'     => null,
                'jurnal_ref_id'    => null,
                'jenis_jurnal'     => 'PENUTUP',
                'tipe_penutupan' => $tipePenutupan,
                'tanggal'          => $tanggal,
                'keterangan'       => self::TIPE_LABELS[$tipePenutupan] . ' — ' . $periode->nama_periode,
                'status'           => $status,
            ]);

            $this->storeDetail($jurnal, $detail);

            return $jurnal->load('detailJurnal.akun');
        });
    }

    public function storeAllTahap(
        Periode $periode,
        array   $semua,
        string  $tanggal,
        string  $status = 'DRAFT'
    ): array {
        $hasil = [];
        foreach ($semua as $tipe => $detail) {
            $hasil[$tipe] = $this->storeTahap($periode, $tipe, $detail, $tanggal, $status);
        }
        return $hasil;
    }

    // ── Post semua draft sekaligus ─────────────────────────────────────────

    public function postSemua(Periode $periode): bool|string
    {
        $jurnals = Jurnal::where('periode_id', $periode->id)
            ->where('jenis_jurnal', 'PENUTUP')
            ->where('status', 'DRAFT')
            ->get();

        if ($jurnals->isEmpty()) {
            return 'Tidak ada jurnal penutup draft yang bisa diposting';
        }

        DB::transaction(fn() => $jurnals->each(fn($j) => $j->update(['status' => 'POSTED'])));

        return true;
    }
}