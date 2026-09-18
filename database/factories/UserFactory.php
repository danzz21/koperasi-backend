<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username'          => fake()->unique()->userName(),
            'email'             => fake()->unique()->safeEmail(),
            'password'          => static::$password ??= Hash::make('password'),
            'nama_lengkap'      => fake()->name(),
            'nomor_ktp'         => fake()->unique()->numerify('################'),
            'nomor_hp'          => fake()->numerify('08##########'),
            'nomor_hp_keluarga' => fake()->numerify('08##########'),
            'foto'              => null,
            'role'              => 'anggota',
            'status'            => 'aktif',
            'remember_token'    => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'   => 'admin',
            'status' => 'aktif',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
