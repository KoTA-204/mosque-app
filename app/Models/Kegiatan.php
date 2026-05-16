<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'jenis_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'anggaran',
        'status',
        'panitia_id',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'anggaran' => 'decimal:2',
    ];

    // ── Konstanta ──────────────────────────────────────────────
    const JENIS = ['QURBAN', 'ZAKAT', 'KAJIAN', 'SOSIAL', 'LAINNYA'];
 
    const STATUS_DRAFT      = 'DRAFT';
    const STATUS_BERJALAN   = 'BERJALAN';
    const STATUS_SELESAI    = 'SELESAI';
    const STATUS_DIBATALKAN = 'DIBATALKAN';
 
    const STATUS = [
        self::STATUS_DRAFT,
        self::STATUS_BERJALAN,
        self::STATUS_SELESAI,
        self::STATUS_DIBATALKAN,
    ];
 
    // ── Relationships ──────────────────────────────────────────
    public function panitia()
    {
        return $this->belongsTo(User::class, 'panitia_id');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }

    // ── Scopes ─────────────────────────────────────────────────
 
    /**
     * Hanya kegiatan yang aktif (tidak dibatalkan).
     */
    public function scopeAktif($query)
    {
        return $query->where('status', '!=', self::STATUS_DIBATALKAN);
    }
 
    /**
     * Filter kegiatan berdasarkan panitia — untuk role Panitia Khusus.
     */
    public function scopeMilikPanitia($query, int $userId)
    {
        return $query->where('panitia_id', $userId);
    }
 
    // ── Helpers ────────────────────────────────────────────────
 
    public function hasTransaksi(): bool
    {
        return $this->transaksi()->exists();
    }
 
    public function isDibatalkan(): bool
    {
        return $this->status === self::STATUS_DIBATALKAN;
    }
 
    public function isSelesai(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }
 
    /**
     * Kegiatan masih bisa menerima transaksi baru.
     */
    public function bisaInputTransaksi(): bool
    {
        return $this->status === self::STATUS_BERJALAN;
    }
 
    /**
     * Total realisasi transaksi approved.
     */
    public function totalRealisasi(): float
    {
        return (float) $this->transaksi()
            ->where('status_approval', 'APPROVED')
            ->sum('jumlah');
    }
}
