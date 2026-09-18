<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jenis_kelamin')->nullable()->after('nomor_hp_keluarga');
            $table->string('pekerjaan')->nullable()->after('jenis_kelamin');
            $table->text('alamat')->nullable()->after('pekerjaan');
            $table->string('no_rek')->nullable()->after('alamat');
            $table->string('atasnama_rekening')->nullable()->after('no_rek');
            $table->string('jenis_bank')->nullable()->after('atasnama_rekening');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin', 'pekerjaan', 'alamat', 'no_rek', 'atasnama_rekening', 'jenis_bank']);
        });
    }
};
