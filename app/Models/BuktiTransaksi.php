<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuktiTransaksi extends Model
{
    use HasFactory;

    protected $table = 'bukti_transaksi';

    protected $fillable = [
        'transaksi_id',
        'nama_file',
        'path_file',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}
