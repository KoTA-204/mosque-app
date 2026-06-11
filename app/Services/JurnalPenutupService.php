<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JurnalPenutupService extends JurnalService
{
    // ─────────────────────────────────────────────────────────────
    // Konstanta
    // ─────────────────────────────────────────────────────────────

    const KODE_ASET_NETO_TANPA_PEMBATASAN  = '3-1000';
    const KODE_ASET_NETO_DENGAN_PEMBATASAN = '3-2000';

    const KODE_KATEGORI_PENDAPATAN = '4';
    const KODE_KATEGORI_BEBAN      = '5';

    const PREFIX_DENGAN_PEMBATASAN = ['4-2'];

    const TIPE_LABELS = [
        'TUTUP_PENDAPATAN' => 'Tutup Pendapatan',
        'TUTUP_BEBAN'      => 'Tutup Beban',
    ];

    // ─────────────────────────────────────────────────────────────
    // Lifecycle Periode
    // ─────────────────────────────────────────────────────────────

    /**
     * Periode dianggap CLOSED jika tidak aktif.
     */
    public function isPeriodeClosed(Periode $periode): bool
    {
        return !$periode->status;
    }

    /**
     * Tutup periode saat ini.
     */
    public function closePeriode(Periode $periode): void
    {
        $periode->update(['status' => false]);
    }

    /**
     * Ambil periode berikutnya berdasarkan tanggal awal.
     */
    public function getNextPeriode(Periode $periode): ?Periode
    {
        return Periode::where('tanggal_awal', '>', $periode->tanggal_awal)
            ->orderBy('tanggal_awal')
            ->first();
    }

    /**
     * Aktifkan periode berikutnya.
     */
    public function activateNextPeriode(Periode $periode): void
    {
        $next = $this->getNextPeriode($periode);

        if (!$next) {
            throw new \RuntimeException(
                'Periode berikutnya belum tersedia.'
            );
        }

        Periode::query()->update(['status' => false]);

        $next->update(['status' => true]);
    }

    /**
     * Finalisasi closing:
     * - tutup periode sekarang
     * - aktifkan periode berikutnya
     */
    public function finalizeClosing(Periode $periode): void
    {
        $this->closePeriode($periode);
        $this->activateNextPeriode($periode);
    }

    // ─────────────────────────────────────────────────────────────
    // Guard Terpusat
    // ─────────────────────────────────────────────────────────────

    /**
     * Guard: periode berikutnya harus sudah ada sebelum closing.
     */
    private function guardPeriodeBerikutnya(Periode $periode): ?string
    {
        if (!$this->getNextPeriode($periode)) {
            return 'Periode berikutnya belum tersedia. '
                 . 'Buat periode berikutnya terlebih dahulu sebelum menutup periode ini.';
        }

        return null;
    }

    /**
     * Guard Opsi B: periode siap ditutup jika
     * - ada minimal 1 jurnal operasional berstatus POSTED, dan
     * - tidak ada satupun jurnal operasional yang masih DRAFT.
     */
    public function guardPeriodeSiapTutup(Periode $periode): ?string
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

    // ─────────────────────────────────────────────────────────────
    // Query
    // ─────────────────────────────────────────────────────────────

    public function getList(
        ?string $search    = '',
        ?string $periodeId = '',
        ?string $status    = '',
        int     $perPage   = 10
    ) {
        return Jurnal::with(['periode', 'detailJurnal.akun'])
            ->penutup()
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->when($status, fn($q) => $q->where('status', strtoupper($status)))
            ->when($search, fn($q) => $q->where('keterangan', 'like', "%{$search}%"))
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getById(Jurnal $jurnal): Jurnal
    {
        return $jurnal->load('periode', 'detailJurnal.akun');
    }

    // ─────────────────────────────────────────────────────────────
    // Ringkasan Saldo Periode
    // ─────────────────────────────────────────────────────────────

    public function getRingkasanPeriode(Periode $periode): array
    {
        if ($this->isPeriodeClosed($periode)) {
            throw new \RuntimeException('Periode sudah ditutup.');
        }

        // Guard Opsi B — dikembalikan sebagai data, bukan throw,
        // supaya view bisa menampilkan pesan yang tepat.
        $pesanTidakSiap = $this->guardPeriodeSiapTutup($periode);

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

        return [
            'pendapatan'       => $pendapatan->values(),
            'beban'            => $beban->values(),
            'total_pendapatan' => $totalPendapatan,
            'total_beban'      => $totalBeban,
            'surplus'          => $totalPendapatan - $totalBeban,
            'pesan_tidak_siap' => $pesanTidakSiap, // null = periode siap ditutup
        ];
    }

    private function akumulasiSaldo(
        Collection $collection,
        $akun,
        $detail,
        string $saldoNormal
    ): void {
        $key = $akun->id;

        if (!$collection->has($key)) {
            $collection->put($key, [
                'akun'  => $akun,
                'saldo' => 0,
            ]);
        }

        $item = $collection->get($key);

        $item['saldo'] += $detail->tipe === $saldoNormal
            ? (float) $detail->nominal
            : -(float) $detail->nominal;

        $collection->put($key, $item);
    }

    // ─────────────────────────────────────────────────────────────
    // Klasifikasi Dana
    // ─────────────────────────────────────────────────────────────

    private function resolveKlasifikasi(Akun $akun): string
    {
        foreach (self::PREFIX_DENGAN_PEMBATASAN as $prefix) {
            if (str_starts_with($akun->kode_akun, $prefix)) {
                return 'DENGAN_PEMBATASAN';
            }
        }

        return 'TANPA_PEMBATASAN';
    }

    private function resolveAkunAsetNetoByKlasifikasi(
        string $klasifikasi
    ): ?Akun {
        $kode = $klasifikasi === 'DENGAN_PEMBATASAN'
            ? self::KODE_ASET_NETO_DENGAN_PEMBATASAN
            : self::KODE_ASET_NETO_TANPA_PEMBATASAN;

        return Akun::where('kode_akun', $kode)->first();
    }

    // ─────────────────────────────────────────────────────────────
    // Status Tahap
    // ─────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────
    // Generate Tutup Pendapatan
    // ─────────────────────────────────────────────────────────────

    public function generateTutupPendapatan(array $ringkasan): array
    {
        if ($ringkasan['total_pendapatan'] <= 0) {
            return [];
        }

        $grouped = collect($ringkasan['pendapatan'])
            ->filter(fn($item) => $item['saldo'] > 0)
            ->groupBy(fn($item) => $this->resolveKlasifikasi($item['akun']));

        $detail = [];

        foreach ($grouped as $klasifikasi => $items) {
            $totalKlasifikasi = $items->sum('saldo');
            $asetNetoAkun     = $this->resolveAkunAsetNetoByKlasifikasi($klasifikasi);

            if (!$asetNetoAkun || $totalKlasifikasi <= 0) continue;

            foreach ($items as $item) {
                $detail[] = $this->buatEntri(
                    $item['akun']->id,
                    $item['akun']->nama_akun,
                    $item['akun']->kode_akun,
                    'DEBIT',
                    $item['saldo']
                );
            }

            $detail[] = $this->buatEntri(
                $asetNetoAkun->id,
                $asetNetoAkun->nama_akun,
                $asetNetoAkun->kode_akun,
                'KREDIT',
                $totalKlasifikasi
            );
        }

        return $detail;
    }

    // ─────────────────────────────────────────────────────────────
    // Generate Tutup Beban
    // ─────────────────────────────────────────────────────────────

    public function generateTutupBeban(array $ringkasan): array
    {
        if ($ringkasan['total_beban'] <= 0) {
            return [];
        }

        $asetNetoAkun = Akun::where(
            'kode_akun',
            self::KODE_ASET_NETO_TANPA_PEMBATASAN
        )->first();

        if (!$asetNetoAkun) {
            return [];
        }

        $detail = [];

        $detail[] = $this->buatEntri(
            $asetNetoAkun->id,
            $asetNetoAkun->nama_akun,
            $asetNetoAkun->kode_akun,
            'DEBIT',
            $ringkasan['total_beban']
        );

        foreach ($ringkasan['beban'] as $item) {
            if ($item['saldo'] <= 0) continue;

            $detail[] = $this->buatEntri(
                $item['akun']->id,
                $item['akun']->nama_akun,
                $item['akun']->kode_akun,
                'KREDIT',
                $item['saldo']
            );
        }

        return $detail;
    }

    private function buatEntri(
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

    // ─────────────────────────────────────────────────────────────
    // Store Semua Tahap (hanya DRAFT)
    // ─────────────────────────────────────────────────────────────

    public function storeAllTahap(
        Periode $periode,
        array   $semua,
        string  $tanggal,
        string  $status = 'DRAFT'
    ): array {
        if ($this->isPeriodeClosed($periode)) {
            throw new \RuntimeException('Periode sudah ditutup.');
        }

        return DB::transaction(function () use ($periode, $semua, $tanggal, $status) {
            $hasil = [];

            foreach ($semua as $tipe => $detail) {
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

                // Hapus draft lama
                Jurnal::where('periode_id', $periode->id)
                    ->where('jenis_jurnal', 'PENUTUP')
                    ->where('tipe_penutupan', $tipe)
                    ->where('status', 'DRAFT')
                    ->each(function ($j) {
                        $j->detailJurnal()->delete();
                        $j->delete();
                    });

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

                $this->storeDetail($jurnal, $detail);

                $hasil[$tipe] = $jurnal->load('detailJurnal.akun');
            }

            return $hasil;
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Posting Existing Draft
    // ─────────────────────────────────────────────────────────────

    public function postExistingDraft(Periode $periode): bool|string
    {
        if ($this->isPeriodeClosed($periode)) {
            return 'Periode sudah ditutup.';
        }

        if ($err = $this->guardPeriodeSiapTutup($periode)) {
            return $err;
        }

        if ($err = $this->guardPeriodeBerikutnya($periode)) {
            return $err;
        }

        DB::transaction(function () use ($periode) {
            Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('status', 'DRAFT')
                ->update(['status' => 'POSTED']);

            $this->finalizeClosing($periode);
        });

        return true;
    }

    // ─────────────────────────────────────────────────────────────
    // Store dan Posting (atomik, tanpa nested transaction)
    // ─────────────────────────────────────────────────────────────

    public function storeAndPost(
        Periode $periode,
        array   $semua,
        string  $tanggal
    ): bool|string {
        if ($this->isPeriodeClosed($periode)) {
            return 'Periode sudah ditutup.';
        }

        if ($err = $this->guardPeriodeSiapTutup($periode)) {
            return $err;
        }

        if ($err = $this->guardPeriodeBerikutnya($periode)) {
            return $err;
        }

        DB::transaction(function () use ($periode, $semua, $tanggal) {
            // Loop di-inline agar satu transaction mencakup store + finalizeClosing
            // (menghindari nested transaction dari storeAllTahap)
            foreach ($semua as $tipe => $detail) {
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

                // Hapus draft lama jika ada
                Jurnal::where('periode_id', $periode->id)
                    ->where('jenis_jurnal', 'PENUTUP')
                    ->where('tipe_penutupan', $tipe)
                    ->where('status', 'DRAFT')
                    ->each(function ($j) {
                        $j->detailJurnal()->delete();
                        $j->delete();
                    });

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

                $this->storeDetail($jurnal, $detail);
            }

            $this->finalizeClosing($periode);
        });

        return true;
    }
}