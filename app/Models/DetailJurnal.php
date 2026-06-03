<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailJurnal extends Model
{
    use HasFactory;

    protected $table = 'detail_jurnal';

    protected $fillable = [
        'jurnal_id',
        'akun_id',
        'tipe',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────

    /**
     * Relasi ke jurnal (supertype).
     * Many to one — 1 jurnal punya banyak baris detail.
     */
    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class);
    }

    /**
     * Relasi ke akun di chart of account.
     */
    public function akun()
    {
        return $this->belongsTo(Akun::class);
    }

    // ── Scope ─────────────────────────────────────────────────────────────

    public function scopeDebit($query)
    {
        return $query->where('tipe', 'DEBIT');
    }

    public function scopeKredit($query)
    {
        return $query->where('tipe', 'KREDIT');
    }
}