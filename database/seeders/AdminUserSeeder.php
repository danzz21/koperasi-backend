<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama_lengkap' => 'Administrator',
                'email' => 'admin@koperasi.local',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'aktif',
                'nomor_ktp' => '0000000000000000',
                'nomor_hp' => '0000000000',
            ]
        );
    }
}
