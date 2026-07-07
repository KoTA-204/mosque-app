<?php

namespace App\Services\Akuntansi;

use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;

abstract class JurnalService
{
    // ── Periode ────────────────────────────────────────────────────────────

    public function getPeriodeAktif(): ?Periode
    {
        return Periode::aktif()->where('tipe', 'bulanan')->latest('tanggal_awal')->first();
    }

    public function getPeriodeList()
    {
        return Periode::orderBy('tanggal_awal', 'desc')->get();
    }

    // ── Parsing nominal ────────────────────────────────────────────────────
    protected function parseNominal(mixed $raw): float
    {
        if (is_string($raw)) {
            return (float) str_replace(['.', ','], ['', '.'], $raw);
        }

        return (float) ($raw ?? 0);
    }

    // ── Simpan detail jurnal ───────────────────────────────────────────────
    protected function storeDetail(Jurnal $jurnal, array $detail): void
    {
        foreach ($detail as $row) {
            $nominal = $this->parseNominal($row['nominal'] ?? 0);

            if (empty($row['akun_id']) || $nominal <= 0) {
                continue;
            }

            DetailJurnal::create([
                'jurnal_id' => $jurnal->id,
                'akun_id'   => $row['akun_id'],
                'tipe'      => $row['tipe'],
                'nominal'   => $nominal,
            ]);
        }
    }

    // ── Validasi balance debit/kredit ──────────────────────────────────────
    protected function isBalanced(Jurnal $jurnal): bool
    {
        $totalDebit  = $jurnal->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = $jurnal->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');

        return round($totalDebit, 2) === round($totalKredit, 2);
    }

    // ── Post ───────────────────────────────────────────────────────────────
    public function post(Jurnal $jurnal): bool|string
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
            $this->onPosted($jurnal);
        });

        return true;
    }

    protected function onPosted(Jurnal $jurnal): void {}

    // ── Delete ─────────────────────────────────────────────────────────────
    public function delete(Jurnal $jurnal): bool|string
    {
        if ($jurnal->status === 'POSTED') {
            return 'Jurnal yang sudah diposting tidak bisa dihapus';
        }

        DB::transaction(function () use ($jurnal) {
            $this->beforeDelete($jurnal);
            $jurnal->detailJurnal()->delete();
            $jurnal->delete();
        });

        return true;
    }

    protected function beforeDelete(Jurnal $jurnal): void {}
}