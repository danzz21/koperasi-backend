<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simpanans', function (Blueprint $table) {
            $table->index(['id_anggota', 'status', 'jenis'], 'simpanans_member_status_jenis_index');
        });

        Schema::table('pinjamen', function (Blueprint $table) {
            $table->index(['id_anggota', 'status'], 'pinjamen_member_status_index');
        });

        Schema::table('cicilans', function (Blueprint $table) {
            $table->index(['id_anggota', 'status'], 'cicilans_member_status_index');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->index(['id_anggota', 'jenis', 'status'], 'pembayarans_member_jenis_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('simpanans', function (Blueprint $table) {
            $table->dropIndex('simpanans_member_status_jenis_index');
        });

        Schema::table('pinjamen', function (Blueprint $table) {
            $table->dropIndex('pinjamen_member_status_index');
        });

        Schema::table('cicilans', function (Blueprint $table) {
            $table->dropIndex('cicilans_member_status_index');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropIndex('pembayarans_member_jenis_status_index');
        });
    }
};
