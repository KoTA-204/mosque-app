<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'anggaran'        => 'decimal:2',
    ];

    // ── Konstanta ──────────────────────────────────────────────
    const JENIS = ['QURBAN', 'ZAKAT', 'KAJIAN', 'SOSIAL', 'LAINNYA'];

    const STATUS_AKTIF   = 'AKTIF';
    const STATUS_DITUTUP = 'DITUTUP';

    const STATUS = [
        self::STATUS_AKTIF,
        self::STATUS_DITUTUP,
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
    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeMilikPanitia($query, int $userId)
    {
        return $query->where('panitia_id', $userId);
    }

    // ── Helpers ────────────────────────────────────────────────
    public function hasTransaksi(): bool
    {
        return $this->transaksi()->exists();
    }

    public function isAktif(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    public function isDitutup(): bool
    {
        return $this->status === self::STATUS_DITUTUP;
    }

    public function bisaInputTransaksi(): bool
    {
        if ($this->status !== self::STATUS_AKTIF) return false;

        // Kalau tanggal sudah lewat, panitia tidak bisa catat transaksi baru
        return ! $this->tanggalSudahSelesai();
    }

        public function totalRealisasi(): float
    {
        return (float) $this->transaksi()
            ->where('status_approval', 'APPROVED')
            ->sum('jumlah');
    }

    /**
     * Cek apakah tanggal kegiatan sudah selesai (lewat hari ini).
     * Jika tidak ada tanggal_selesai, gunakan tanggal_mulai sebagai acuan.
     */
    public function tanggalSudahSelesai(): bool
    {
        $acuan = $this->tanggal_selesai ?? $this->tanggal_mulai;

        return $acuan !== null && $acuan->isPast();
    }

    // ── Status otomatis ────────────────────────────────────────

    /**
     * Tutup kegiatan otomatis jika:
     *  1. Semua transaksi sudah APPROVED, DAN
     *  2. Tanggal kegiatan sudah selesai (lewat hari ini).
     *
     * Dipanggil setiap kali bendahara approve sebuah transaksi,
     * dan juga oleh scheduled command harian.
     */
    public function tutupJikaSelesai(): void
    {
        if ($this->status === self::STATUS_DITUTUP) return;

        // Harus ada minimal 1 transaksi
        if (! $this->transaksi()->exists()) return;

        // Syarat 1: tidak ada transaksi yang belum APPROVED
        $adaBelumApproved = $this->transaksi()
            ->whereNotNull('status_approval')        
            ->whereNotIn('status_approval', ['APPROVED'])
            ->exists();

        if ($adaBelumApproved) return;

        // Syarat 2: tanggal kegiatan sudah selesai / lewat hari ini
        if (! $this->tanggalSudahSelesai()) return;

        $this->update(['status' => self::STATUS_DITUTUP]);
    }

    /**
     * Buka kembali kegiatan jika ada transaksi yang di-reject atau revision.
     * Dipanggil setiap kali bendahara reject/revision sebuah transaksi.
     */
    public function bukaKembali(): void
    {
        if ($this->status === self::STATUS_AKTIF) return;

        $this->update(['status' => self::STATUS_AKTIF]);
    }
}