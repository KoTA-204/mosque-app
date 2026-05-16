<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriAkun extends Model
{
    use HasFactory;

    protected $table = 'kategori_akun';

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
    ];

    public function akunKeuangan()
    {
        return $this->hasMany(Akun::class);
    }
}
