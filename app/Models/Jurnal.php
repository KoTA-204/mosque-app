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
        'tipe_penutupan',
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

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function jurnalRef()
    {
        return $this->belongsTo(Jurnal::class, 'jurnal_ref_id');
    }

    public function jurnalKoreksi()
    {
        return $this->hasMany(Jurnal::class, 'jurnal_ref_id');
    }

    public function aset()
    {
        return $this->belongsToMany(Aset::class, 'jurnal_aset', 'jurnal_id', 'aset_id')
            ->withPivot('nominal');
    }

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

    public function getKodeJurnalAttribute(): string
    {
        if (!$this->id || !$this->tanggal || !$this->jenis_jurnal) {
            return '—';
        }

        $prefix = match (strtoupper($this->jenis_jurnal)) {
            'PEMBUKA'     => 'JP',
            'UMUM'        => 'JU',
            'PENYESUAIAN' => 'JPS',
            'KOREKSI'     => 'JK',
            'PENUTUP'     => 'JPT',
            default       => 'JX',
        };

        $tahun     = $this->tanggal->format('Y');
        $bulan     = $this->tanggal->format('m');
        $nomorUrut = str_pad($this->id, 3, '0', STR_PAD_LEFT);

        return strtoupper($this->jenis_jurnal) === 'PENUTUP'
            ? "{$prefix}-{$tahun}-{$nomorUrut}"
            : "{$prefix}-{$tahun}-{$bulan}-{$nomorUrut}";
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

    public function scopePembuka($query)
    {
        return $query->where('jenis_jurnal', 'PEMBUKA');
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