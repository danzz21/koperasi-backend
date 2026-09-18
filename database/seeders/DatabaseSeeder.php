<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin default
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@koperasi.com',
                'password' => Hash::make('admin123'),
                'nama_lengkap' => 'Admin Koperasi',
                'nomor_ktp' => '1111111111111111',
                'nomor_hp' => '081234567890',
                'role' => 'admin',
                'status' => 'aktif',
            ]
        );

        // Anggota contoh
        $anggota = User::updateOrCreate(
            ['username' => 'anggota1'],
            [
                'email' => 'anggota1@koperasi.com',
                'password' => Hash::make('anggota123'),
                'nama_lengkap' => 'Budi Santoso',
                'nomor_ktp' => '2222222222222222',
                'nomor_hp' => '082345678901',
                'nomor_hp_keluarga' => '082345678902',
                'role' => 'anggota',
                'status' => 'aktif',
            ]
        );

        $now = now();
        DB::table('simpanans')->updateOrInsert(
            ['id_anggota' => $anggota->id, 'jenis' => 'pokok'],
            ['nominal' => 500000, 'status' => 'aktif', 'keterangan' => 'Simpanan pokok awal', 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('simpanans')->updateOrInsert(
            ['id_anggota' => $anggota->id, 'jenis' => 'wajib'],
            ['nominal' => 600000, 'status' => 'aktif', 'keterangan' => 'Simpanan wajib sampai bulan ini', 'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('simpanans')->updateOrInsert(
            ['id_anggota' => $anggota->id, 'jenis' => 'sukarela'],
            ['nominal' => 250000, 'status' => 'aktif', 'keterangan' => 'Simpanan sukarela', 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('pinjamen')->updateOrInsert(
            ['id_anggota' => $anggota->id, 'tipe' => 'qard'],
            [
                'nominal' => 3000000,
                'sisa_pinjaman' => 2250000,
                'tenor' => 12,
                'bunga_per_bulan' => 0,
                'angsuran' => 250000,
                'cicilan_ke' => 3,
                'status' => 'aktif',
                'tgl_persetujuan' => $now->toDateString(),
                'tgl_cair' => $now->toDateString(),
                'keterangan' => 'Pinjaman modal usaha',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        $pinjaman = DB::table('pinjamen')->where('id_anggota', $anggota->id)->where('tipe', 'qard')->first();
        for ($ke = 1; $ke <= 4; $ke++) {
            DB::table('cicilans')->updateOrInsert(
                ['id_pinjaman' => $pinjaman->id, 'cicilan_ke' => $ke],
                [
                    'id_anggota' => $anggota->id,
                    'nominal' => 250000,
                    'tgl_tempo' => $now->copy()->subMonths(4 - $ke)->addMonth()->toDateString(),
                    'tgl_bayar' => $ke <= 3 ? $now->copy()->subMonths(4 - $ke)->toDateString() : null,
                    'denda' => 0,
                    'status' => $ke <= 3 ? 'terbayar' : 'belumbayar',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        DB::table('ppob_transaksi')->updateOrInsert(
            ['id_anggota' => $anggota->id, 'ref_id' => 'DEMO-PPOB-001'],
            [
                'jenis_produk' => 'pulsa',
                'provider' => 'Telkomsel',
                'nomor_tujuan' => '081234567890',
                'nominal' => 50000,
                'harga' => 52000,
                'kode_produk' => 'TSEL50',
                'nama_produk' => 'Telkomsel Rp 50.000',
                'status' => 'success',
                'payment_ref' => 'DEMO-PAY-001',
                'payment_method' => 'simulasi',
                'keterangan' => 'Transaksi demo',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Setting akad default
        $akadDefaults = [
            ['key' => 'murabahah_margin', 'value' => '10',  'keterangan' => 'Margin Murabahah (%)'],
            ['key' => 'mudharabah_ratio', 'value' => '60',  'keterangan' => 'Rasio Bagi Hasil Mudharabah (%)'],
            ['key' => 'qard_margin',      'value' => '0',   'keterangan' => 'Margin Qard (%)'],
        ];

        foreach ($akadDefaults as $akad) {
            DB::table('akad')->updateOrInsert(
                ['key' => $akad['key']],
                $akad
            );
        }
    }
}
