<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $fillable = [
        'dompet_id',
        'kegiatan_id',
        'user_id',
        'kategori_transaksi_id',
        'tanggal_transaksi',
        'jenis_transaksi',
        'jumlah',
        'deskripsi',
        'no_referensi',
        'status_persetujuan',
        'catatan',
        'status_jurnal',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'jumlah' => 'decimal:2',
    ];

    const STATUS_DRAFT    = 'DRAFT';
    const STATUS_PENDING  = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_REVISION = 'REVISION';

    const STATUS_EDITABLE = [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_REVISION];

    const JENIS_TRANSAKSI_PEMASUKAN = 'PEMASUKAN';
    const JENIS_TRANSAKSI_PENGELUARAN = 'PENGELUARAN';

    public function bisaDiedit(): bool
    {
        return in_array($this->status_persetujuan, self::STATUS_EDITABLE, true);
    }

    public function dompet()
    {
        return $this->belongsTo(Dompet::class);
    }

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kategoriTransaksi()
    {
        return $this->belongsTo(KategoriTransaksi::class);
    }

    public function kencleng()
    {
        return $this->hasOne(Kencleng::class);
    }

    public function buktiTransaksi()
    {
        return $this->hasMany(BuktiTransaksi::class);
    }

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class);
    }
 
    public function aset()
    {
        return $this->hasOne(Aset::class);
    }
 
    // ─── Scope ───────────────────────────────────────────────────────────────
    
    public function scopePeriodeAktif($query)
    {
        $periode = \App\Models\Periode::aktif()->first();

        if (!$periode) {
            // Tidak ada periode
            return $query->whereRaw('1 = 0');
        }
        
        return $query->whereBetween('tanggal_transaksi', [
            $periode->tanggal_awal,
            $periode->tanggal_akhir,
        ]);
    }
 
    // ─── Accessor ────────────────────────────────────────────────────────────
 
    public function getJumlahFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }
}
