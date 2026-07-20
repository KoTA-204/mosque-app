<?php

namespace App\Services\Akuntansi;

use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

abstract class JurnalService
{
    public function getPeriodeAktif(): ?Periode
    {
        return Periode::aktif()->where('tipe', 'bulanan')->latest('tanggal_awal')->first();
    }

    public function getPeriodeList()
    {
        return Periode::orderBy('tanggal_awal', 'desc')->get();
    }

    public function getPeriodeOpenList()
    {
        return Periode::aktif()->orderBy('tanggal_awal', 'desc')->get();
    }

    protected function parseNominal(mixed $raw): float
    {
        if (is_string($raw)) {
            $raw = str_replace(['.', ','], ['', '.'], $raw);
        }
        return round((float) ($raw ?? 0), 2);
    }

    protected function catatDetailJurnal(Jurnal $jurnal, array $detail): void
    {
        foreach ($detail as $row) {
            $nominal = $this->parseNominal($row['nominal'] ?? 0);

            if (empty($row['akun_id']) || $nominal <= 0) {
                continue;
            }

            $jurnal->tambahDetail($row['akun_id'], $row['tipe'], $nominal);
        }
    }

    // ── Validasi keseimbangan (getter/pengecek — dipertahankan) ───────────

    public function isBalanced(Jurnal $jurnal): bool
    {
        $debit  = (int) round($jurnal->detailJurnal->where('tipe', 'DEBIT')->sum('nominal')  * 100);
        $kredit = (int) round($jurnal->detailJurnal->where('tipe', 'KREDIT')->sum('nominal') * 100);
        return $debit > 0 && $debit === $kredit;
    }

    /**
     * Cek keseimbangan dari raw detail (sebelum jurnal dibuat).
     * Menggantikan detailSeimbang() yang dulu terduplikasi di JurnalPembukaService
     * dan di beberapa controller.
     */
    public function isDetailSeimbang(array $detail): bool
    {
        $debit = $kredit = 0;
        foreach ($detail as $row) {
            $c = (int) round($this->parseNominal($row['nominal'] ?? 0) * 100);
            if (($row['tipe'] ?? null) === 'DEBIT')  $debit  += $c;
            if (($row['tipe'] ?? null) === 'KREDIT') $kredit += $c;
        }
        return $debit > 0 && $debit === $kredit;
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

    protected function setelahPosting(Jurnal $jurnal): void {}

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

    protected function sebelumPenghapusan(Jurnal $jurnal): void {}

    abstract public function daftar(array $filter): LengthAwarePaginator;
}
