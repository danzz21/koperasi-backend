<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pinjamen', function (Blueprint $table) {
            $table->enum('status', ['pending', 'disetujui', 'aktif', 'lunas', 'ditolak'])
                ->default('pending')->change();
        });

        Schema::table('simpanans', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'tidak_aktif', 'ditolak', 'ditarik'])
                ->default('aktif')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pinjamen', function (Blueprint $table) {
            $table->enum('status', ['pending', 'aktif', 'lunas', 'ditolak'])
                ->default('pending')->change();
        });

        Schema::table('simpanans', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'tidak_aktif', 'ditolak'])
                ->default('aktif')->change();
        });
    }
};
