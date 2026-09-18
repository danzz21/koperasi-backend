<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('simpanans', function (Blueprint $table) {
            $table->id();
                       $table->bigInteger('id_anggota')->unsigned();
                       $table->enum('jenis', ['pokok', 'wajib', 'sukarela'])->default('pokok');
                       $table->decimal('nominal', 15, 2)->default(0);
                       $table->decimal('bunga', 15, 2)->default(0);
                       $table->text('keterangan')->nullable();
                       $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
                       $table->foreign('id_anggota')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simpanans');
    }
};
