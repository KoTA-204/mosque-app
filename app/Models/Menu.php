<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_name',
        'route_name',
        'icon',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')
                    ->orderBy('sort_order');
    }
}
