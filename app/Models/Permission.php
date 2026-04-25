<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'permission_code',
        'permission_name',
        'module',
        'action',
        'description',
        'is_active',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class);
    }
}
