<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'jenis_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'anggaran',
        'status',
        'panitia_id'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'anggaran' => 'decimal:2'
    ];

    public function panitia()
    {
        return $this->belongsTo(User::class, 'panitia_id');
    }
}