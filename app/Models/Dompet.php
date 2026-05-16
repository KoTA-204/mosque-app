<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dompet extends Model
{
    use HasFactory;

    protected $table = 'dompet';

    protected $fillable = [
        'nama_dompet',
        'jenis_dompet',
        'nomor_rekening',
        'nama_bank',
        'saldo_awal',
    ];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
