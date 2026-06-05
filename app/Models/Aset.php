<?php

namespace App\Models;

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

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function getPenyusutanPerBulanAttribute(): float
    {
        if (!$this->umur_manfaat || $this->umur_manfaat <= 0) return 0;
        return (float) $this->nilai_tercatat / ($this->umur_manfaat * 12);
    }

    public function getAkumulasiRealTimeAttribute(): float
    {
        if (!$this->tanggal_mulai_penyusutan || !$this->umur_manfaat) return 0;
        $bulan = (int) $this->tanggal_mulai_penyusutan->diffInMonths(now());
        return min($this->penyusutan_per_bulan * $bulan, (float) $this->nilai_tercatat);
    }

    public function getNilaiBukuRealTimeAttribute(): float
    {
        return max((float) $this->nilai_tercatat - $this->akumulasi_real_time, 0);
    }

    public function getProgressPenyusutanAttribute(): float
    {
        if (!(float) $this->nilai_tercatat) return 0;
        return min(($this->akumulasi_real_time / (float) $this->nilai_tercatat) * 100, 100);
    }

    public function getPenyusutanPerTahunAttribute(): float
    {
        if (!$this->umur_manfaat || $this->umur_manfaat <= 0) return 0;
        return (float) $this->nilai_tercatat / $this->umur_manfaat;
    }

    /**
     * Format: ASET-{YYYY}-{NNN} — nomor urut per tahun perolehan.
     * Tahun diambil dari tanggal_perolehan yang dikirim, bukan now().
     */
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

    public function getLabelPemberiAttribute(): string
    {
        return match($this->sumber_perolehan) {
            'Wakaf'        => 'Nama Wakif',
            'Hibah/Donasi' => 'Nama Donatur / Pemberi Hibah',
            'Infak Jamaah' => 'Nama Pemberi Infak',
            default        => 'Nama Pemberi',
        };
    }

    public function getLabelNilaiAttribute(): string
    {
        return match($this->sumber_perolehan) {
            'Pembelian'    => 'Nilai Perolehan',
            'Wakaf'        => 'Nilai Wajar Aset',
            'Hibah/Donasi' => 'Nilai Wajar Aset',
            'Infak Jamaah' => 'Nilai Perolehan',
            default        => 'Nilai Perolehan',
        };
    }

    public function getPemilikAsetAttribute(): string
    {
        return 'Masjid Lukmanul Hakim';
    }
}