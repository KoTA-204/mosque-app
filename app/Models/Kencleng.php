<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kencleng extends Model
{
    use HasFactory;

    protected $table = 'kencleng';

    protected $fillable = [
        'transaksi_id',
        'nomor_berita_acara',
        'file_berita_acara',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function detail()
    {
        return $this->hasMany(KenclengDetail::class);
    }
}
