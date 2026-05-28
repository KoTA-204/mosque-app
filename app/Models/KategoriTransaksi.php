<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriTransaksi extends Model
{
    use HasFactory;

    protected $table = 'kategori_transaksi';

    protected $fillable = [
        'nama_kategori',
        'jenis_transaksi',
        'deskripsi',
        'status',
    ];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
