<?php

namespace App\Services;

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

    /**
     * Parse nominal dari string format "1.000.000,50" atau angka biasa
     * menjadi float.
     */
    protected function parseNominal(mixed $raw): float
    {
        if (is_string($raw)) {
            return (float) str_replace(['.', ','], ['', '.'], $raw);
        }

        return (float) ($raw ?? 0);
    }

    // ── Simpan detail jurnal ───────────────────────────────────────────────

    /**
     * Iterasi $data['detail'] dan buat DetailJurnal.
     * Baris dengan akun_id kosong atau nominal <= 0 dilewati.
     */
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

    /**
     * Kembalikan true jika total debit == total kredit (presisi 2 desimal).
     */
    protected function isBalanced(Jurnal $jurnal): bool
    {
        $totalDebit  = $jurnal->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = $jurnal->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');

        return round($totalDebit, 2) === round($totalKredit, 2);
    }

    // ── Post ───────────────────────────────────────────────────────────────

    /**
     * Posting jurnal: validasi status, minimal entri, dan balance.
     * Subclass boleh meng-override untuk logika tambahan (mis. update aset).
     */
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

    /**
     * Hook yang dipanggil setelah jurnal berhasil diposting (dalam transaksi).
     * Subclass dapat meng-override untuk side-effect tambahan.
     */
    protected function onPosted(Jurnal $jurnal): void
    {
        // default: tidak ada aksi tambahan
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    /**
     * Hapus jurnal beserta detail-nya.
     * Subclass dapat meng-override untuk detach relasi tambahan (mis. aset).
     */
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

    /**
     * Hook sebelum jurnal dihapus (dalam transaksi).
     * Subclass dapat meng-override untuk detach relasi tambahan.
     */
    protected function beforeDelete(Jurnal $jurnal): void
    {
        // default: tidak ada aksi tambahan
    }
}