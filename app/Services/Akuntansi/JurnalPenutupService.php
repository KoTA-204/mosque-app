<?php

namespace App\Services\Akuntansi;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service tutup buku.
 *
 * Fokus (High Cohesion): menyusun & mencatat jurnal penutup + ringkasan periode.
 * Seluruh transisi status periode didelegasikan ke PeriodeService (Indirection).
 */
class JurnalPenutupService extends JurnalService
{
    // Aset neto tanpa pembatasan — LEAF penampung surplus/defisit dana umum.
    // (Bukan header 3-1000. Menutup ke header membuat laporan yang membaca saldo
    //  akun LEAF tidak menemukan nilainya — inilah sumber bug penutupan sebelumnya.)
    const KODE_DANA_UMUM = '3-1200';

    const KODE_KATEGORI_PENDAPATAN = '4';
    const KODE_KATEGORI_BEBAN      = '5';

    // Peta prefix kode PENDAPATAN → kode akun dana (aset neto) tujuan penutupan.
    // Pendapatan terikat mengalir ke dana-nya masing-masing; kode yang tidak
    // terdaftar di sini dianggap TANPA pembatasan → KODE_DANA_UMUM.
    const PETA_DANA_PENDAPATAN = [
        '4-21' => '3-2300', // Zakat Fitrah → Dana Zakat
        '4-22' => '3-2300', // Zakat Maal   → Dana Zakat
        '4-23' => '3-2400', // Qurban       → Dana Qurban
        '4-24' => '3-2200', // Pembangunan  → Dana Pembangunan
        '4-25' => '3-2100', // Wakaf        → Dana Wakaf
    ];

    // Peta prefix kode BEBAN penyaluran dana terikat → kode akun dana sumbernya.
    // Dipakai menyusun tahap "pelepasan aset neto dari pembatasan"
    // (net assets released from restrictions) sesuai ISAK 35.
    const PETA_DANA_BEBAN_TERIKAT = [
        '5-4' => '3-2300', // Penyaluran Zakat (per asnaf) → dari Dana Zakat
    ];

    const TIPE_LABELS = [
        'TUTUP_PENDAPATAN'     => 'Tutup Pendapatan',
        'TUTUP_BEBAN'          => 'Tutup Beban',
        'PELEPASAN_PEMBATASAN' => 'Pelepasan Aset Neto dari Pembatasan',
    ];

    public function __construct(private PeriodeService $periode) {}

    // ── Query ────────────────────────────────────────

    public function daftar(array $filter): LengthAwarePaginator
    {
        return Jurnal::with(['periode', 'detailJurnal.akun'])
            ->penutup()
            ->when($filter['periode_id'] ?? null, fn($q) => $q->where('periode_id', $filter['periode_id']))
            ->when($filter['status'] ?? null,    fn($q) => $q->where('status', strtoupper($filter['status'])))
            ->when($filter['search'] ?? null,    fn($q) => $q->where('keterangan', 'like', "%{$filter['search']}%"))
            ->orderBy('tanggal', 'desc')
            ->paginate($filter['per_page'] ?? 10)
            ->withQueryString();
    }

    public function getById(Jurnal $jurnal): Jurnal
    {
        return $jurnal->load('periode', 'detailJurnal.akun');
    }

    // ── Guard kesiapan jurnal (tetap di sini — tentang jurnal, bukan periode) ──

    /**
     * Periode siap ditutup jika:
     * - ada minimal 1 jurnal operasional POSTED, dan
     * - tidak ada jurnal operasional yang masih DRAFT.
     */
    public function validasiPeriodeSiapTutup(Periode $periode): ?string
    {
        $adaPosted = Jurnal::where('periode_id', $periode->id)
            ->whereIn('jenis_jurnal', ['UMUM', 'PENYESUAIAN', 'KOREKSI'])
            ->where('status', 'POSTED')
            ->exists();

        if (!$adaPosted) {
            return 'Periode belum memiliki jurnal yang diposting. '
                 . 'Catat minimal satu transaksi dan posting sebelum menutup periode.';
        }

        $adaDraft = Jurnal::where('periode_id', $periode->id)
            ->whereIn('jenis_jurnal', ['UMUM', 'PENYESUAIAN', 'KOREKSI'])
            ->where('status', 'DRAFT')
            ->exists();

        if ($adaDraft) {
            return 'Masih ada jurnal yang belum diposting. '
                 . 'Posting semua jurnal terlebih dahulu sebelum menutup periode.';
        }

        return null;
    }

    // ── Ringkasan saldo periode ───────────────────────────

    /**
     * Ringkasan pendapatan/beban periode.
     * Catatan: `pesan_tidak_siap` di sini murni untuk DATA TAMPILAN di halaman
     * create (bukan guard). Guard yang otoritatif berjalan sekali di
     * catatDanPostingPenutupan / postingDraftPenutupan / catatSemuaTahapPenutupan.
     */
    public function getRingkasanPeriode(Periode $periode): array
    {
        if ($this->periode->isPeriodeClosed($periode)) {
            throw new \RuntimeException('Periode sudah ditutup.');
        }

        $pesanTidakSiap = $this->validasiPeriodeSiapTutup($periode);

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
                    $this->hitungAkumulasiSaldo($pendapatan, $akun, $detail, 'KREDIT');
                }

                if ($kodeKat === self::KODE_KATEGORI_BEBAN) {
                    $this->hitungAkumulasiSaldo($beban, $akun, $detail, 'DEBIT');
                }
            }
        }

        $totalPendapatan = $pendapatan->sum('saldo');
        $totalBeban      = $beban->sum('saldo');

        return [
            'pendapatan'       => $pendapatan->values(),
            'beban'            => $beban->values(),
            'total_pendapatan' => $totalPendapatan,
            'total_beban'      => $totalBeban,
            'surplus'          => $totalPendapatan - $totalBeban,
            'pesan_tidak_siap' => $pesanTidakSiap,
        ];
    }

    private function hitungAkumulasiSaldo(
        Collection $collection,
        $akun,
        $detail,
        string $saldoNormal
    ): void {
        $key = $akun->id;

        if (!$collection->has($key)) {
            $collection->put($key, ['akun' => $akun, 'saldo' => 0]);
        }

        $item = $collection->get($key);

        $item['saldo'] += $detail->tipe === $saldoNormal
            ? (float) $detail->nominal
            : -(float) $detail->nominal;

        $collection->put($key, $item);
    }

    // ── Klasifikasi dana ───────────────────────────────

    /**
     * Tentukan KODE akun dana (aset neto) tujuan penutupan sebuah akun
     * pendapatan berdasarkan peta prefix. Tidak cocok → dana umum.
     */
    private function tentukanKodeDanaPendapatan(Akun $akun): string
    {
        foreach (self::PETA_DANA_PENDAPATAN as $prefix => $kodeDana) {
            if (str_starts_with($akun->kode_akun, $prefix)) {
                return $kodeDana;
            }
        }

        return self::KODE_DANA_UMUM;
    }

    // ── Status tahap ─────────────────────────────────

    public function getStatusTahap(Periode $periode): array
    {
        $status = [];

        foreach (array_keys(self::TIPE_LABELS) as $tipe) {
            $jurnal = Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('tipe_penutupan', $tipe)
                ->latest()
                ->first();

            $ada     = (bool) $jurnal;
            $selesai = $ada && $jurnal->status === 'POSTED';

            $status[$tipe] = [
                'jurnal'  => $jurnal,
                'selesai' => $selesai,
                'ada'     => $ada,
                'status'  => $jurnal?->status,
            ];
        }

        return $status;
    }

    public function getTahapSelesai(Periode $periode): int
    {
        $status  = $this->getStatusTahap($periode);
        $selesai = 0;

        foreach (array_keys(self::TIPE_LABELS) as $tipe) {
            if ($status[$tipe]['selesai']) {
                $selesai++;
            }
        }

        return $selesai;
    }

    public function getExistingDraft(Periode $periode): array
    {
        $result = [];

        foreach (array_keys(self::TIPE_LABELS) as $tipe) {
            $jurnal = Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('tipe_penutupan', $tipe)
                ->where('status', 'DRAFT')
                ->latest()
                ->first();

            if ($jurnal) {
                $result[$tipe] = $jurnal->load('detailJurnal.akun');
            }
        }

        return $result;
    }

    // ── Menyusun entri jurnal penutup ──────────────────────

    public function susunJurnalTutupPendapatan(array $ringkasan): array
    {
        if ($ringkasan['total_pendapatan'] <= 0) {
            return [];
        }

        // Kelompokkan pendapatan berdasarkan KODE akun dana tujuannya, sehingga
        // tiap dana (umum, zakat, qurban, pembangunan, wakaf) ditutup ke akun
        // LEAF-nya sendiri — bukan ke header seperti implementasi sebelumnya.
        $grouped = collect($ringkasan['pendapatan'])
            ->filter(fn($item) => $item['saldo'] > 0)
            ->groupBy(fn($item) => $this->tentukanKodeDanaPendapatan($item['akun']));

        $detail = [];

        foreach ($grouped as $kodeDana => $items) {
            $totalDana = $items->sum('saldo');
            $danaAkun  = Akun::where('kode_akun', $kodeDana)->first();

            if (!$danaAkun || $totalDana <= 0) continue;

            // DEBIT tiap akun pendapatan (menutup saldo kreditnya) ...
            foreach ($items as $item) {
                $detail[] = $this->buatEntriJurnal(
                    $item['akun']->id,
                    $item['akun']->nama_akun,
                    $item['akun']->kode_akun,
                    'DEBIT',
                    $item['saldo']
                );
            }

            // ... KREDIT ke akun dana (LEAF) tujuannya.
            $detail[] = $this->buatEntriJurnal(
                $danaAkun->id,
                $danaAkun->nama_akun,
                $danaAkun->kode_akun,
                'KREDIT',
                $totalDana
            );
        }

        return $detail;
    }

    public function susunJurnalTutupBeban(array $ringkasan): array
    {
        if ($ringkasan['total_beban'] <= 0) {
            return [];
        }

        $danaUmum = Akun::where('kode_akun', self::KODE_DANA_UMUM)->first();

        if (!$danaUmum) {
            return [];
        }

        $detail = [];

        // Seluruh beban (termasuk penyaluran zakat) ditutup ke Dana Umum
        // (aset neto tanpa pembatasan) sesuai ISAK 35. Bagian yang didanai dana
        // terikat "dikembalikan" lewat tahap PELEPASAN_PEMBATASAN di bawah.
        $detail[] = $this->buatEntriJurnal(
            $danaUmum->id,
            $danaUmum->nama_akun,
            $danaUmum->kode_akun,
            'DEBIT',
            $ringkasan['total_beban']
        );

        foreach ($ringkasan['beban'] as $item) {
            if ($item['saldo'] <= 0) continue;

            $detail[] = $this->buatEntriJurnal(
                $item['akun']->id,
                $item['akun']->nama_akun,
                $item['akun']->kode_akun,
                'KREDIT',
                $item['saldo']
            );
        }

        return $detail;
    }

    /**
     * Tahap-3 (ISAK 35): pelepasan aset neto dari pembatasan.
     *
     * Saat dana terikat (mis. zakat) disalurkan, bebannya sudah ditutup ke Dana
     * Umum pada tahap Tutup Beban. Di sini kita mereklasifikasi sebesar nilai
     * penyaluran itu dari dana terikat → Dana Umum, sehingga:
     *   - Dana Umum tidak ikut berkurang oleh beban dana terikat, dan
     *   - saldo dana terikat berkurang sebesar yang sudah tersalurkan.
     * Nilai ini tampil sebagai baris "Aset neto yang dibebaskan dari pembatasan"
     * pada Laporan Perubahan Aset Neto.
     */
    public function susunJurnalPelepasanPembatasan(array $ringkasan): array
    {
        $danaUmum = Akun::where('kode_akun', self::KODE_DANA_UMUM)->first();
        if (!$danaUmum) {
            return [];
        }

        $detail = [];

        foreach (self::PETA_DANA_BEBAN_TERIKAT as $prefixBeban => $kodeDana) {
            $totalTersalur = collect($ringkasan['beban'])
                ->filter(fn($item) =>
                    $item['saldo'] > 0 &&
                    str_starts_with($item['akun']->kode_akun, $prefixBeban)
                )
                ->sum('saldo');

            if ($totalTersalur <= 0) continue;

            $danaTerikat = Akun::where('kode_akun', $kodeDana)->first();
            if (!$danaTerikat) continue;

            // DEBIT dana terikat (kurangi pembatasan) ...
            $detail[] = $this->buatEntriJurnal(
                $danaTerikat->id,
                $danaTerikat->nama_akun,
                $danaTerikat->kode_akun,
                'DEBIT',
                $totalTersalur
            );

            // ... KREDIT Dana Umum (aset neto dibebaskan ke tanpa pembatasan).
            $detail[] = $this->buatEntriJurnal(
                $danaUmum->id,
                $danaUmum->nama_akun,
                $danaUmum->kode_akun,
                'KREDIT',
                $totalTersalur
            );
        }

        return $detail;
    }

    private function buatEntriJurnal(
        int    $akunId,
        string $namaAkun,
        string $kodeAkun,
        string $tipe,
        float  $nominal
    ): array {
        return [
            'akun_id'   => $akunId,
            'akun'      => $namaAkun,
            'kode_akun' => $kodeAkun,
            'tipe'      => $tipe,
            'nominal'   => $nominal,
        ];
    }

    // ── Mencatat semua tahap (DRAFT) ──────────────────────

    public function catatSemuaTahapPenutupan(
        Periode $periode,
        array   $semua,
        string  $tanggal,
        string  $status = 'DRAFT'
    ): array {
        if ($this->periode->isPeriodeClosed($periode)) {
            throw new \RuntimeException('Periode sudah ditutup.');
        }

        // Guard kesiapan tutup dijalankan sekali (otoritatif) untuk jalur ini.
        if ($err = $this->validasiPeriodeSiapTutup($periode)) {
            throw new \RuntimeException($err);
        }

        return DB::transaction(function () use ($periode, $semua, $tanggal, $status) {
            $hasil = [];

            foreach ($semua as $tipe => $detail) {
                $this->pastikanBelumDiposting($periode, $tipe);
                $this->hapusDraftLama($periode, $tipe);

                $jurnal = Jurnal::create([
                    'periode_id'     => $periode->id,
                    'transaksi_id'   => null,
                    'jurnal_ref_id'  => null,
                    'jenis_jurnal'   => 'PENUTUP',
                    'tipe_penutupan' => $tipe,
                    'tanggal'        => $tanggal,
                    'keterangan'     => self::TIPE_LABELS[$tipe] . ' — ' . $periode->nama_periode,
                    'status'         => $status,
                ]);

                $this->catatDetailJurnal($jurnal, $detail);

                $hasil[$tipe] = $jurnal->load('detailJurnal.akun');
            }

            return $hasil;
        });
    }

    // ── Posting draft yang sudah ada ──────────────────────

    public function postingDraftPenutupan(Periode $periode): bool|string
    {
        if ($this->periode->isPeriodeClosed($periode)) {
            return 'Periode sudah ditutup.';
        }

        if ($err = $this->validasiPeriodeSiapTutup($periode)) {
            return $err;
        }

        if ($err = $this->periode->validasiPeriodeBerikutnya($periode)) {
            return $err;
        }

        DB::transaction(function () use ($periode) {
            Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('status', 'DRAFT')
                ->update(['status' => 'POSTED']);

            $this->periode->finalisasiPenutupan($periode);
        });

        return true;
    }

    // ── Catat & posting sekaligus (atomik) ───────────────────

    public function catatDanPostingPenutupan(
        Periode $periode,
        array   $semua,
        string  $tanggal
    ): bool|string {
        if ($this->periode->isPeriodeClosed($periode)) {
            return 'Periode sudah ditutup.';
        }

        if ($err = $this->validasiPeriodeSiapTutup($periode)) {
            return $err;
        }

        if ($err = $this->periode->validasiPeriodeBerikutnya($periode)) {
            return $err;
        }

        DB::transaction(function () use ($periode, $semua, $tanggal) {
            foreach ($semua as $tipe => $detail) {
                $this->pastikanBelumDiposting($periode, $tipe);
                $this->hapusDraftLama($periode, $tipe);

                $jurnal = Jurnal::create([
                    'periode_id'     => $periode->id,
                    'transaksi_id'   => null,
                    'jurnal_ref_id'  => null,
                    'jenis_jurnal'   => 'PENUTUP',
                    'tipe_penutupan' => $tipe,
                    'tanggal'        => $tanggal,
                    'keterangan'     => self::TIPE_LABELS[$tipe] . ' — ' . $periode->nama_periode,
                    'status'         => 'POSTED',
                ]);

                $this->catatDetailJurnal($jurnal, $detail);
            }

            $this->periode->finalisasiPenutupan($periode);
        });

        return true;
    }

    // ── Helper internal (menghapus duplikasi antara dua alur di atas) ───────

    private function pastikanBelumDiposting(Periode $periode, string $tipe): void
    {
        $sudahPosted = Jurnal::where('periode_id', $periode->id)
            ->where('jenis_jurnal', 'PENUTUP')
            ->where('tipe_penutupan', $tipe)
            ->where('status', 'POSTED')
            ->exists();

        if ($sudahPosted) {
            throw new \RuntimeException(
                'Tahap ' . (self::TIPE_LABELS[$tipe] ?? $tipe) .
                ' sudah diposting dan tidak dapat diulang.'
            );
        }
    }

    private function hapusDraftLama(Periode $periode, string $tipe): void
    {
        Jurnal::where('periode_id', $periode->id)
            ->where('jenis_jurnal', 'PENUTUP')
            ->where('tipe_penutupan', $tipe)
            ->where('status', 'DRAFT')
            ->each(function ($j) {
                $j->detailJurnal()->delete();
                $j->delete();
            });
    }
}
