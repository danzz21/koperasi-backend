<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengajuan pinjaman dari anggota disimpan dengan status 'pending' menunggu
 * persetujuan admin. Sebelumnya enum ini tidak punya nilai 'pending' sehingga
 * hitungan "Pengajuan Pinjaman" di dashboard selalu 0.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pinjamen', function (Blueprint $table) {
            $table->enum('status', ['pending', 'aktif', 'lunas', 'ditolak'])
                ->default('pending')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('pinjamen', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'lunas', 'ditolak'])
                ->default('aktif')
                ->change();
        });
    }
};
