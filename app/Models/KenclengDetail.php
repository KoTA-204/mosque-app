<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KenclengDetail extends Model
{
    use HasFactory;

    protected $table = 'kencleng_detail';

    protected $fillable = [
        'kencleng_id',
        'pecahan',
        'jumlah_pecahan',
    ];

    public function kencleng()
    {
        return $this->belongsTo(Kencleng::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->pecahan * $this->jumlah_pecahan;
    }
}
