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
        'tipe_periode',
        'tanggal_awal',
        'tanggal_akhir',
        'status',
    ];

    protected $casts = [
        'tanggal_awal'  => 'date',
        'tanggal_akhir' => 'date',
        'status'        => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class);
    }

    // ── Scope ─────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status', true);
    }
}