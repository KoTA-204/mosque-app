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
        'nilai_buku',
        'akumulasi_penyusutan',
    ];

    protected $casts = [
        'tanggal_perolehan'        => 'date',
        'tanggal_mulai_penyusutan' => 'date',
        'nilai_tercatat'           => 'decimal:2',
        'nilai_buku'               => 'decimal:2',
        'akumulasi_penyusutan'     => 'decimal:2',
    ];

    // filter daftar aset
    public function scopeFilter(Builder $query, array $filters): Builder
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

    // penyusutan per bulan
    public function getPenyusutanPerBulanAttribute(): float
    {
        if (! $this->umur_manfaat || $this->umur_manfaat <= 0) return 0;
        return (float) $this->nilai_tercatat / ($this->umur_manfaat * 12);
    }

    // akumulasi penyusutan real-time
    public function getAkumulasiRealTimeAttribute(): float
    {
        if ($this->status_aset === 'TIDAK AKTIF') {
            return (float) $this->akumulasi_penyusutan;
        }
        if (! $this->tanggal_mulai_penyusutan || ! $this->umur_manfaat) return 0;
        $bulan = (int) $this->tanggal_mulai_penyusutan->diffInMonths(now());
        return min($this->penyusutan_per_bulan * $bulan, (float) $this->nilai_tercatat);
    }

    // nilai buku real-time
    public function getNilaiBukuRealTimeAttribute(): float
    {
        if ($this->status_aset === 'TIDAK AKTIF') {
            return (float) $this->nilai_buku;
        }
        return max((float) $this->nilai_tercatat - $this->akumulasi_real_time, 0);
    }

    // progress penyusutan (%)
    public function getProgressPenyusutanAttribute(): float
    {
        if (! (float) $this->nilai_tercatat) return 0;
        return min(($this->akumulasi_real_time / (float) $this->nilai_tercatat) * 100, 100);
    }

    // penyusutan per tahun
    public function getPenyusutanPerTahunAttribute(): float
    {
        if (! $this->umur_manfaat || $this->umur_manfaat <= 0) return 0;
        return (float) $this->nilai_tercatat / $this->umur_manfaat;
    }

    // generate kode aset: ASET-{YYYY}-{NNN}
    public static function generateKode(string $tanggalPerolehan): string
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

    // relasi jurnal penyesuaian
    public function jurnalPenyesuaian()
    {
        return $this->belongsToMany(Jurnal::class, 'jurnal_aset', 'aset_id', 'jurnal_id')
            ->withPivot('nominal')
            ->orderBy('tanggal', 'desc');
    }

    // pemilik aset
    public function getPemilikAsetAttribute(): string
    {
        return 'Masjid Lukmanul Hakim';
    }
}