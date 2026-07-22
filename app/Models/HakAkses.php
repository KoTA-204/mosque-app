<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class HakAkses extends Model
{
    protected $table = 'hak_akses';

    use HasFactory;

    protected $fillable = [
        'kode_hak_akses',
        'nama_hak_akses',
        'modul',
        'aksi',
        'deskripsi',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function getSlugAttribute(): string
    {
        return Str::slug($this->kode_hak_akses);
    }

    public function peran()
    {
        return $this->belongsToMany(Peran::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class, 'hak_akses_id');
    }
}
