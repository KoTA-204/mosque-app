<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Auth\Notifications\ResetPassword;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'name',
        'email',
        'password',
        'initial_password',
        'status',
        'role_id',
        'credentials_sent_at',
    ];

    protected $hidden = [
        'password',
        'initial_password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'initial_password'    => 'encrypted',
            'credentials_sent_at' => 'datetime',
        ];
    }

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function hasPermission(string $permissionCode): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionCode) {
                $query->where('permission_code', $permissionCode)
                      ->where('is_active', true);
            })
            ->exists();
    }

    public function hasRole(string $roleSlug): bool
    {
        return optional($this->roles)->slug === $roleSlug;
    }

    public function getUsernameAttribute(): string
    {
        return explode('@', $this->email)[0];
    }

    // Cek apakah user sudah pernah mencatat transaksi
    public function hasTransaksi(): bool
    {
        return \App\Models\Transaksi::where('user_id', $this->id)->exists();
    }

    // Apakah kredensial (email + password awal) sudah dikirim ke user?
    public function credentialsSent(): bool
    {
        return $this->credentials_sent_at !== null;
    }
}