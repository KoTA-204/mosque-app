<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'role_name',
        'description',
        'is_active',
    ];

    public function getSlugAttribute(): string
    {
        return Str::slug($this->role_name);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

}
