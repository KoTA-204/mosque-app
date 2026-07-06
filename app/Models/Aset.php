<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aset extends Model
{
    use SoftDeletes;

    protected $table = 'aset';

    // ── Alasan penonaktifan (toggle Tidak Aktif) ───────────────────────────
    const ALASAN_MENGANGGUR   = 'MENGANGGUR';
    const ALASAN_RUSAK_BERAT  = 'RUSAK_BERAT';
    const ALASAN_AKAN_DILEPAS = 'AKAN_DILEPAS';

    const ALASAN_NONAKTIF_LABELS = [
        self::ALASAN_MENGANGGUR   => 'Menganggur sementara',
        self::ALASAN_RUSAK_BERAT  => 'Rusak berat',
        self::ALASAN_AKAN_DILEPAS => 'Akan dilepas / dibuang',
    ];

    protected $fillable = [
        'transaksi_id',
        'kode_aset',
        'nama_aset',
        'sumber_perolehan',
        'tanggal_perolehan',
        'nilai_tercatat',
        'umur_manfaat',
        'kondisi_aset',
        'lokasi_aset',
        'nama_pemberi',
        'jumlah_unit',
        'dokumen_pendukung',
        'tanggal_mulai_penyusutan',
        'keterangan',
        'status_aset',
        'alasan_nonaktif',
        'catatan_nonaktif',
        'tanggal_nonaktif',
        'jenis_pelepasan',
        'nilai_buku',
        'akumulasi_penyusutan',
    ];

    protected $casts = [
        'tanggal_perolehan'        => 'date',
        'tanggal_mulai_penyusutan' => 'date',
        'tanggal_nonaktif'         => 'date',
        'nilai_tercatat'           => 'decimal:2',
        'nilai_buku'               => 'decimal:2',
        'akumulasi_penyusutan'     => 'decimal:2',
    ];

    // filter daftar aset
    public function scopeSaring(Builder $query, array $filters): Builder
    {
        $query->when($filters['search'] ?? null, function (Builder $q, $search) {
            $q->where(function (Builder $sub) use ($search) {
                $sub->where('nama_aset',   'like', "%{$search}%")
                    ->orWhere('kode_aset',   'like', "%{$search}%")
                    ->orWhere('lokasi_aset', 'like', "%{$search}%");
            });
        });

        $query->when($filters['tahun'] ?? null, fn (Builder $q, $tahun) =>
            $q->where('kode_aset', 'like', "ASET-{$tahun}-%")
        );

        $query->when($filters['lokasi'] ?? null, fn (Builder $q, $lokasi) =>
            $q->where('lokasi_aset', $lokasi)
        );

        $query->when($filters['sumber'] ?? null, fn (Builder $q, $sumber) =>
            $q->where('sumber_perolehan', $sumber)
        );

        $query->when($filters['status'] ?? null, fn (Builder $q, $status) =>
            $q->where('status_aset', strtoupper($status))
        );

        $query->when($filters['kondisi'] ?? null, fn (Builder $q, $kondisi) =>
            $q->where('kondisi_aset', $kondisi)
        );

        return $query;
    }

    // relasi transaksi
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    // scope aset aktif
    public function scopeAktif($query)
    {
        return $query->where('status_aset', 'AKTIF');
    }

    // ── Aturan status & penyusutan ─────────────────────────────────────────

    /**
     * Apakah aset TETAP disusutkan walau berstatus TIDAK AKTIF?
     * Sesuai PSAK 16 par. 55: aset yang menganggur sementara TETAP disusutkan
     * sampai aset dihentikan pengakuannya (dilepas) atau habis masa manfaatnya.
     */
    public function tetapMenyusut(): bool
    {
        return $this->status_aset === 'TIDAK AKTIF'
            && $this->alasan_nonaktif === self::ALASAN_MENGANGGUR;
    }

    /**
     * Bisakah aset diaktifkan kembali lewat toggle?
     * - AKAN_DILEPAS  : tidak bisa (terminal).
     * - RUSAK_BERAT   : hanya bila kondisinya sudah diperbaiki (bukan RUSAK BERAT).
     */
    public function bisaDiaktifkan(): bool
    {
        if ($this->status_aset !== 'TIDAK AKTIF') {
            return false;
        }
        if ($this->sudahDilepas()) {
            return false;
        }
        if ($this->alasan_nonaktif === self::ALASAN_AKAN_DILEPAS) {
            return false;
        }
        if ($this->alasan_nonaktif === self::ALASAN_RUSAK_BERAT && $this->kondisi_aset === 'RUSAK BERAT') {
            return false;
        }
        return true;
    }

    // penyusutan per bulan
    public function hitungPenyusutanPerBulan(): float
    {
        if (! $this->umur_manfaat || $this->umur_manfaat <= 0) return 0;
        return (float) $this->nilai_tercatat / ($this->umur_manfaat * 12);
    }

    // akumulasi penyusutan real-time
    public function hitungAkumulasiRealTime(): float
    {
        // Aset TIDAK AKTIF dengan alasan terminal (rusak berat / akan dilepas)
        // memakai snapshot beku. Aset menganggur sementara TETAP menyusut.
        if ($this->status_aset === 'TIDAK AKTIF' && ! $this->tetapMenyusut()) {
            return (float) $this->akumulasi_penyusutan;
        }
        if (! $this->tanggal_mulai_penyusutan || ! $this->umur_manfaat) return 0;
        $bulan = (int) $this->tanggal_mulai_penyusutan->diffInMonths(now());
        return min($this->hitungPenyusutanPerBulan() * $bulan, (float) $this->nilai_tercatat);
    }

    // nilai buku real-time
    public function hitungNilaiBukuRealTime(): float
    {
        if ($this->status_aset === 'TIDAK AKTIF' && ! $this->tetapMenyusut()) {
            return (float) $this->nilai_buku;
        }
        return max((float) $this->nilai_tercatat - $this->hitungAkumulasiRealTime(), 0);
    }

    // progress penyusutan (%)
    public function hitungProgressPenyusutan(): float
    {
        if (! (float) $this->nilai_tercatat) return 0;
        return min(($this->hitungAkumulasiRealTime() / (float) $this->nilai_tercatat) * 100, 100);
    }

    // penyusutan per tahun
    public function hitungPenyusutanPerTahun(): float
    {
        if (! $this->umur_manfaat || $this->umur_manfaat <= 0) return 0;
        return (float) $this->nilai_tercatat / $this->umur_manfaat;
    }

    // generate kode aset: ASET-{YYYY}-{NNN}
    public static function buatKode(string $tanggalPerolehan): string
    {
        $tahun  = date('Y', strtotime($tanggalPerolehan));
        $prefix = "ASET-{$tahun}-";
        $last   = self::where('kode_aset', 'like', "{$prefix}%")
            ->orderByDesc('kode_aset')
            ->value('kode_aset');
        $no = $last ? ((int) substr($last, -3)) + 1 : 1;
        return $prefix . str_pad($no, 3, '0', STR_PAD_LEFT);
    }

    // label nama pemberi sesuai sumber
    public function getLabelPemberiAttribute(): string
    {
        return match ($this->sumber_perolehan) {
            'Wakaf'        => 'Nama Wakif',
            'Hibah/Donasi' => 'Nama Donatur / Pemberi Hibah',
            'Infak Jamaah' => 'Nama Pemberi Infak',
            default        => 'Nama Pemberi',
        };
    }

    // label nilai sesuai sumber
    public function getLabelNilaiAttribute(): string
    {
        return match ($this->sumber_perolehan) {
            'Pembelian'    => 'Nilai Perolehan',
            'Wakaf'        => 'Nilai Wajar Aset',
            'Hibah/Donasi' => 'Nilai Wajar Aset',
            'Infak Jamaah' => 'Nilai Perolehan',
            default        => 'Nilai Perolehan',
        };
    }

    // label alasan nonaktif (untuk tampilan)
    public function getLabelAlasanNonaktifAttribute(): ?string
    {
        if (! $this->alasan_nonaktif) return null;
        return self::ALASAN_NONAKTIF_LABELS[$this->alasan_nonaktif] ?? $this->alasan_nonaktif;
    }

    // relasi jurnal penyesuaian
    public function jurnalPenyesuaian()
    {
        return $this->belongsToMany(Jurnal::class, 'jurnal_aset', 'aset_id', 'jurnal_id')
            ->withPivot('nominal')
            ->orderBy('tanggal', 'desc');
    }

    /**
     * Aset dianggap SUDAH DILEPAS bila memiliki jurnal penyesuaian
     * bertipe PELEPASAN_ASET yang sudah diposting. Tidak memakai status baru.
     */
    public function sudahDilepas(): bool
    {
        return $this->jurnalPenyesuaian()
            ->where('tipe_penyesuaian', 'PELEPASAN_ASET')
            ->where('status', 'POSTED')
            ->exists();
    }

    // scope: aset yang belum pernah dilepas (untuk pilihan pelepasan)
    public function scopeBelumDilepas($query)
    {
        return $query->whereDoesntHave('jurnalPenyesuaian', function ($q) {
            $q->where('tipe_penyesuaian', 'PELEPASAN_ASET')->where('status', 'POSTED');
        });
    }

    // pemilik aset
    public function getPemilikAsetAttribute(): string
    {
        return 'Masjid Lukmanul Hakim';
    }
}
