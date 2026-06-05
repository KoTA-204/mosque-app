<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Aset extends Model
{
    use HasFactory;

    protected $table = 'aset';

    protected $fillable = [
        'transaksi_id',
        'nama_aset',
        'sumber_perolehan',
        'tanggal_perolehan',
        'nilai_tercatat',
        'umur_manfaat',
        'kondisi_aset',
        'lokasi_aset',
        'nama_pemberi',
        'status_aset',
        'nilai_buku',
        'akumulasi_penyusutan',
    ];

    protected $casts = [
        'tanggal_perolehan' => 'date',
        'nilai_tercatat' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
        'akumulasi_penyusutan' => 'decimal:2',
        'umur_manfaat' => 'integer',
    ];

    /**
     * Relasi ke transaksi.
     */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function jurnal()
    {
        return $this->belongsToMany(Jurnal::class);
    }

    /**
     * Scope aset aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aset', 'AKTIF');
    }

    /**
     * Scope aset draft.
     */
    public function scopeDraft($query)
    {
        return $query->where('status_aset', 'DRAFT');
    }

    /**
     * Scope aset tidak aktif.
     */
    public function scopeTidakAktif($query)
    {
        return $query->where('status_aset', 'TIDAK AKTIF');
    }
}
