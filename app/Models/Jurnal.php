<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jurnal extends Model
{
    use HasFactory;

    protected $table = 'jurnal';

    protected $fillable = [
        'periode_id',
        'transaksi_id',
        'jurnal_ref_id',
        'jenis_jurnal',
        'tipe_penyesuaian',
        'tanggal',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    /**
     * Relasi ke transaksi operasional.
     * Hanya ada nilainya jika jenis_jurnal = UMUM.
     * One to one.
     */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    /**
     * Self referencing — jurnal yang dikoreksi oleh jurnal ini.
     * Hanya ada nilainya jika jenis_jurnal = KOREKSI.
     * Many to one (banyak koreksi bisa mengacu ke 1 jurnal).
     */
    public function jurnalRef()
    {
        return $this->belongsTo(Jurnal::class, 'jurnal_ref_id');
    }

    /**
     * Self referencing — semua jurnal koreksi yang mengacu ke jurnal ini.
     * One to many.
     */
    public function jurnalKoreksi()
    {
        return $this->hasMany(Jurnal::class, 'jurnal_ref_id');
    }

    public function aset()
    {
        return $this->belongsToMany(Aset::class, 'jurnal_aset', 'jurnal_id', 'aset_id')
            ->withPivot('nominal');
    }

    /**
     * Baris debit/kredit jurnal ini.
     * One to many.
     */
    public function detailJurnal()
    {
        return $this->hasMany(DetailJurnal::class);
    }

    // ── Accessor ──────────────────────────────────────────────────────────

    public function getTotalDebitAttribute(): float
    {
        return $this->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
    }

    public function getTotalKreditAttribute(): float
    {
        return $this->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');
    }

    public function getIsBalanceAttribute(): bool
    {
        return round($this->total_debit, 2) === round($this->total_kredit, 2);
    }

    // ── Scope ─────────────────────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->where('status', 'DRAFT');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'POSTED');
    }

    public function scopeUmum($query)
    {
        return $query->where('jenis_jurnal', 'UMUM');
    }

    public function scopePenyesuaian($query)
    {
        return $query->where('jenis_jurnal', 'PENYESUAIAN');
    }

    public function scopeKoreksi($query)
    {
        return $query->where('jenis_jurnal', 'KOREKSI');
    }

    public function scopePenutup($query)
    {
        return $query->where('jenis_jurnal', 'PENUTUP');
    }
}