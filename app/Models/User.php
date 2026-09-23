<?php

namespace App\Models;

use App\Support\AuthUserPayload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'google_id',
        'avatar',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'profile_completed_at' => 'datetime',
        'password'             => 'hashed',
    ];

    /**
     * Tandai profil sebagai lengkap otomatis begitu semua field wajib terisi.
     * Berlaku juga bila data dilengkapi lewat panel admin Filament.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->profile_completed_at !== null) {
                return;
            }

            // Admin/superadmin tidak pernah diminta onboarding
            if ($user->role !== 'anggota') {
                $user->profile_completed_at = now();

                return;
            }

            if ($user->missingProfileFields() === []) {
                $user->profile_completed_at = now();
            }
        });
    }

    /** Apakah data profil sudah pernah dinyatakan lengkap? */
    public function hasCompletedProfile(): bool
    {
        return $this->profile_completed_at !== null;
    }

    /** Anggota yang belum melengkapi data (mis. hasil login Google) wajib onboarding. */
    public function requiresProfileCompletion(): bool
    {
        return $this->role === 'anggota' && ! $this->hasCompletedProfile();
    }

    /**
     * Daftar field wajib yang masih kosong / masih berupa placeholder.
     *
     * @return array<int, string>
     */
    public function missingProfileFields(): array
    {
        $missing = [];

        foreach (AuthUserPayload::REQUIRED_PROFILE_FIELDS as $field => $label) {
            if (! $this->hasProfileFieldValue($field)) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    private function hasProfileFieldValue(string $field): bool
    {
        $value = $this->{$field};

        if (is_string($value)) {
            $value = trim($value);

            if ($value === '' || $value === '-') {
                return false;
            }
        }

        if ($value === null || $value === '') {
            return false;
        }

        return match ($field) {
            // Placeholder akun Google ("G" + digit) belum dianggap KTP valid
            'nomor_ktp'     => (bool) preg_match('/^\d{16}$/', (string) $value),
            'nomor_hp'      => strlen((string) preg_replace('/\D/', '', (string) $value)) >= 9,
            'jenis_kelamin' => in_array(strtoupper((string) $value), ['L', 'P'], true),
            default         => true,
        };
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return in_array($this->role, ['admin', 'superadmin']);
        }

        if ($panel->getId() === 'superadmin') {
            return $this->role === 'superadmin';
        }

        return false;
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'superadmin']);
    }

    public function getFilamentName(): string
    {
        return $this->nama_lengkap ?: $this->username ?: $this->email;
    }

    public function anggota()
    {
        return $this->hasOne(Anggota::class, 'id_anggota', 'id');
    }

    /** Perangkat passkey (WebAuthn) milik user. */
    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkey::class, 'user_id');
    }
}
