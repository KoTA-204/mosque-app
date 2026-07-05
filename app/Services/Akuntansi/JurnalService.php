<?php

namespace App\Services\Akuntansi;

use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Kelas induk abstrak untuk seluruh service jurnal.
 *
 * Menampung logika bersama semua jenis jurnal:
 * - referensi periode (getter)
 * - penulisan baris detail (didelegasikan ke model Jurnal — GRASP Creator)
 * - validasi keseimbangan debit/kredit (getter/pengecek)
 * - posting & penghapusan (Template Method + hook)
 * - posting massal (DRY)
 *
 * Kontrak: setiap turunan WAJIB menyediakan daftar().
 * Cukup 1 abstract class — tidak memerlukan interface terpisah.
 */
abstract class JurnalService
{
    // ── Referensi periode (getter — dipertahankan) ─────────────────────

    public function getPeriodeAktif(): ?Periode
    {
        return Periode::aktif()->where('tipe', 'bulanan')->latest('tanggal_awal')->first();
    }

    public function getPeriodeList()
    {
        return Periode::orderBy('tanggal_awal', 'desc')->get();
    }

    // ── Util nominal ────────────────────────────────────────

    protected function parseNominal(mixed $raw): float
    {
        if (is_string($raw)) {
            return (float) str_replace(['.', ','], ['', '.'], $raw);
        }

        return (float) ($raw ?? 0);
    }

    // ── Mencatat baris detail (Creator: Jurnal yang mencipta) ─────────────

    protected function catatDetailJurnal(Jurnal $jurnal, array $detail): void
    {
        foreach ($detail as $row) {
            $nominal = $this->parseNominal($row['nominal'] ?? 0);

            if (empty($row['akun_id']) || $nominal <= 0) {
                continue;
            }

            // Jurnal sebagai Creator dari DetailJurnal miliknya.
            $jurnal->tambahDetail($row['akun_id'], $row['tipe'], $nominal);
        }
    }

    // ── Validasi keseimbangan (getter/pengecek — dipertahankan) ───────────

    public function isBalanced(Jurnal $jurnal): bool
    {
        $totalDebit  = $jurnal->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = $jurnal->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');

        return abs($totalDebit - $totalKredit) < 0.01;
    }

    /**
     * Cek keseimbangan dari raw detail (sebelum jurnal dibuat).
     * Menggantikan detailSeimbang() yang dulu terduplikasi di JurnalPembukaService
     * dan di beberapa controller.
     */
    public function isDetailSeimbang(array $detail): bool
    {
        $totalDebit = $totalKredit = 0;

        foreach ($detail as $row) {
            $nominal = $this->parseNominal($row['nominal'] ?? 0);
            if (($row['tipe'] ?? null) === 'DEBIT')  $totalDebit  += $nominal;
            if (($row['tipe'] ?? null) === 'KREDIT') $totalKredit += $nominal;
        }

        return abs($totalDebit - $totalKredit) < 0.01;
    }

    // ── Posting ke buku besar (Template Method) ──────────────────────

    public function postingKeBukuBesar(Jurnal $jurnal): bool|string
    {
        if ($jurnal->status === 'POSTED') {
            return 'Jurnal sudah diposting';
        }

        $jurnal->load('detailJurnal');

        if ($jurnal->detailJurnal->isEmpty()) {
            return 'Jurnal harus memiliki minimal satu entri';
        }

        if (!$this->isBalanced($jurnal)) {
            return 'Total debit dan kredit harus sama sebelum diposting';
        }

        DB::transaction(function () use ($jurnal) {
            $jurnal->update(['status' => 'POSTED']);
            $this->setelahPosting($jurnal);
        });

        return true;
    }

    /**
     * Posting massal beberapa jurnal DRAFT sekaligus.
     * Terpusat di induk (DRY) dan memakai Template Method postingKeBukuBesar()
     * sehingga hook setelahPosting() tetap dijalankan tiap jurnal.
     */
    public function postingMassalKeBukuBesar(array $ids): array
    {
        $jurnals = Jurnal::whereIn('id', $ids)
            ->where('status', 'DRAFT')
            ->with('detailJurnal')
            ->get();

        $posted = 0;
        $errors = [];

        foreach ($jurnals as $jurnal) {
            $result = $this->postingKeBukuBesar($jurnal);

            if ($result === true) {
                $posted++;
            } else {
                $errors[] = "Jurnal #{$jurnal->id} gagal: {$result}.";
            }
        }

        $message = "{$posted} jurnal berhasil diposting.";
        if (!empty($errors)) {
            $message .= ' ' . implode(' ', $errors);
        }

        return [
            'success' => $posted > 0,
            'message' => $message,
            'posted'  => $posted,
            'failed'  => count($errors),
        ];
    }

    /** Hook: dijalankan setelah jurnal diposting. */
    protected function setelahPosting(Jurnal $jurnal): void {}

    // ── Menghapus jurnal (Template Method) ──────────────────────────

    public function hapusJurnal(Jurnal $jurnal): bool|string
    {
        if ($jurnal->status === 'POSTED') {
            return 'Jurnal yang sudah diposting tidak bisa dihapus';
        }

        DB::transaction(function () use ($jurnal) {
            $this->sebelumPenghapusan($jurnal);
            $jurnal->detailJurnal()->delete();
            $jurnal->delete();
        });

        return true;
    }

    /** Hook: dijalankan sebelum jurnal dihapus. */
    protected function sebelumPenghapusan(Jurnal $jurnal): void {}

    // ── Kontrak (pengganti interface) ─────────────────────────────

    /** Setiap jenis jurnal wajib menyediakan daftar terfilter. */
    abstract public function daftar(array $filter): LengthAwarePaginator;
}
