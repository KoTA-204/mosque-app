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
        'status_approval',
        'catatan',
        'status_jurnal',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'jumlah' => 'decimal:2',
    ];

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
}
