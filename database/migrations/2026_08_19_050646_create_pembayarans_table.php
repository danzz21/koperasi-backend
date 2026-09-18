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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
                       $table->bigInteger('id_anggota')->unsigned();
                       $table->string('no_referensi')->unique();
                       $table->enum('jenis', ['cicilan', 'simpanan', 'ppob'])->default('cicilan');
                       $table->decimal('nominal', 15, 2);
                       $table->enum('metode', ['transfer', 'tunai', 'e-wallet'])->default('transfer');
                       $table->enum('status', ['pending', 'diverifikasi', 'selesai', 'gagal'])->default('pending');
                       $table->text('keterangan')->nullable();
                       $table->datetime('tgl_pembayaran')->nullable();
                       $table->foreign('id_anggota')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
