<?php

namespace App\Services;

use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Hash;

class SuperadminUserService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function listing(array $filters)
    {
        $query = User::query();

        foreach (['role', 'status'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(function ($subquery) use ($search) {
                $subquery->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate(15);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $user = User::query()->create([
            'nama_lengkap' => $data['nama_lengkap'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => $data['status'] ?? 'aktif',
            'nomor_ktp' => $data['nomor_ktp'] ?? null,
            'nomor_hp' => $data['nomor_hp'] ?? null,
        ]);

        AuditLogger::log('user.create', $user, [], [
            'username' => $user->username,
            'role' => $user->role,
        ]);

        return $user;
    }

    public function find(int|string $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $old = $user->only(['username', 'email', 'role', 'status']);
        $updates = array_intersect_key($data, array_flip([
            'nama_lengkap', 'email', 'username', 'role', 'status', 'nomor_hp',
        ]));

        if (isset($data['password']) && $data['password'] !== '') {
            $updates['password'] = Hash::make($data['password']);
        }

        $user->update($updates);
        AuditLogger::log('user.update', $user, $old, $user->only(['username', 'email', 'role', 'status']));

        return $user;
    }

    public function updateRole(User $user, string $role): User
    {
        $oldRole = $user->role;
        $user->update(['role' => $role]);
        AuditLogger::log('user.update_role', $user, ['role' => $oldRole], ['role' => $user->role]);

        return $user;
    }

    public function delete(User $user): void
    {
        AuditLogger::log('user.delete', $user, $user->only(['username', 'email', 'role']));
        $user->delete();
    }
}
