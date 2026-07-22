<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Peran extends Model
{
    protected $table = 'peran';

    use HasFactory;
    
    protected $fillable = [
        'nama_peran',
        'deskripsi',
    ];

    public function getSlugAttribute(): string
    {
        return Str::slug($this->nama_peran);
    }

    public function pengguna()
    {
        return $this->hasMany(Pengguna::class);
    }

    public function hak_akses()
    {
        return $this->belongsToMany(HakAkses::class);
    }

}
