<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Setoran simpanan (pokok/wajib/sukarela) yang dikirim anggota berstatus
 * 'tidak_aktif' = menunggu verifikasi admin. Nilai 'ditolak' ditambahkan agar
 * penolakan tidak perlu menghapus riwayat setoran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simpanans', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'tidak_aktif', 'ditolak'])
                ->default('aktif')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('simpanans', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'tidak_aktif'])
                ->default('aktif')
                ->change();
        });
    }
};
