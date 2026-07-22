<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Periode extends Model
{
    use HasFactory;

    protected $table = 'periode';

    protected $fillable = [
        'nama_periode',
        'tipe',
        'tanggal_awal',
        'tanggal_akhir',
        'status',
    ];

    protected $casts = [
        'tanggal_awal'  => 'date',
        'tanggal_akhir' => 'date',
        'status'        => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class);
    }

    // ── Scope ───────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status', true);
    }

    /**
     * Periode "berjalan" untuk tampilan default (dashboard, laporan, filter).
     * Dengan multi-periode, beberapa periode bisa open sekaligus, jadi
     * "berjalan" = periode open yang memuat tanggal hari ini.
     * Fallback: periode open terbaru, lalu periode terbaru apa pun.
     */
    public static function berjalan(): ?self
    {
        $hariIni = now()->toDateString();

        return static::where('status', true)
                ->whereDate('tanggal_awal', '<=', $hariIni)
                ->whereDate('tanggal_akhir', '>=', $hariIni)
                ->orderByDesc('tanggal_awal')
                ->first()
            ?? static::where('status', true)
                ->orderByDesc('tanggal_awal')
                ->first()
            ?? static::orderByDesc('tanggal_akhir')
                ->first();
    }

    // ── Perilaku domain periode ───────────────────────────────────

    /**
     * Periode sebelumnya dengan tipe yang sama.
     * Menggantikan query "periode sebelumnya" yang sebelumnya diduplikasi di
     * controller dan beberapa service laporan.
     */
    public function periodeSebelumnya(): ?Periode
    {
        return static::where('tipe', $this->tipe)
            ->where('tanggal_akhir', '<', $this->tanggal_awal)
            ->orderByDesc('tanggal_akhir')
            ->first();
    }

    /**
     * Semua id periode dengan tanggal_akhir <= periode ini.
     * Dipakai untuk perhitungan saldo kumulatif (posisi keuangan).
     * Menggantikan KalkulatorSaldoAkun::getPeriodeIdsUpTo (pemisahan tanggung
     * jawab / ISP: urusan periode ada di model Periode).
     *
     * @return array<int>
     */
    public function getIdsSampaiSekarang(): array
    {
        return static::where('tanggal_akhir', '<=', $this->tanggal_akhir)
            ->pluck('id')
            ->toArray();
    }
}
