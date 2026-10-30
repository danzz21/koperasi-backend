<?php

namespace App\Services;

use App\Models\User;

class AdminMemberService
{
    public function listing()
    {
        return User::query()->where('role', 'anggota')->paginate();
    }

    public function find(int|string $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int|string $id, array $data): User
    {
        $user = $this->find($id);
        $user->update($data);

        return $user;
    }

    public function delete(int|string $id): void
    {
        $this->find($id)->delete();
    }

    public function pending()
    {
        return User::query()->where('role', 'anggota')->where('status', 'pending')->get();
    }
}
