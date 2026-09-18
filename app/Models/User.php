<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nama_lengkap',
        'email',
        'username',
        'password',
        'nomor_ktp',
        'nomor_hp',
        'nomor_hp_keluarga',
        'jenis_kelamin',
        'tgl_lahir',
        'pekerjaan',
        'alamat',
        'no_rek',
        'atasnama_rekening',
        'jenis_bank',
        'role',
        'status',
        'foto',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->role === 'admin';
    }

    public function getFilamentName(): string
    {
        return $this->nama_lengkap ?: $this->username ?: $this->email;
    }

    public function anggota()
    {
        return $this->hasOne(Anggota::class, 'id_anggota', 'id');
    }
}
