<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Akun extends Model
{
    use HasFactory;

    protected $table = 'akun';

    protected $fillable = [
        'kategori_akun_id',
        'parent_id',
        'kode_akun',
        'nama_akun',
        'saldo_normal',
        'deskripsi',
    ];

    public function kategoriAkun()
    {
        return $this->belongsTo(KategoriAkun::class);
    }

    public function parent()
    {
        return $this->belongsTo(Akun::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Akun::class, 'parent_id');
    }
}
