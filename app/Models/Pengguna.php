<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Auth\Notifications\ResetPassword;

class Pengguna extends Authenticatable implements CanResetPasswordContract
{
    protected $table = 'pengguna';

    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'password_awal',
        'status',
        'peran_id',
        'kredensial_dikirim_pada',
    ];

    protected $hidden = [
        'password',
        'password_awal',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'password_awal'    => 'encrypted',
            'kredensial_dikirim_pada' => 'datetime',
        ];
    }

    public function peran()
    {
        return $this->belongsTo(Peran::class, 'peran_id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function hasHakAkses(string $hakAksesCode): bool
    {
        return $this->peran()
            ->whereHas('hak_akses', function ($query) use ($hakAksesCode) {
                $query->where('kode_hak_akses', $hakAksesCode)
                      ->where('aktif', true);
            })
            ->exists();
    }

    public function hasPeran(string $peranSlug): bool
    {
        return optional($this->peran)->slug === $peranSlug;
    }

    public function getUsernameAttribute(): string
    {
        return explode('@', $this->email)[0];
    }

    // Cek apakah pengguna sudah pernah mencatat transaksi
    public function hasTransaksi(): bool
    {
        return \App\Models\Transaksi::where('pengguna_id', $this->id)->exists();
    }

    // Apakah kredensial (email + password awal) sudah dikirim ke pengguna?
    public function credentialsSent(): bool
    {
        return $this->kredensial_dikirim_pada !== null;
    }
}