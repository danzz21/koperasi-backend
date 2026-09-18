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
        Schema::create('cicilans', function (Blueprint $table) {
            $table->id();
                       $table->bigInteger('id_pinjaman')->unsigned();
                       $table->bigInteger('id_anggota')->unsigned();
                       $table->integer('cicilan_ke');
                       $table->decimal('nominal', 15, 2);
                       $table->date('tgl_tempo');
                       $table->date('tgl_bayar')->nullable();
                       $table->decimal('denda', 15, 2)->default(0);
                       $table->enum('status', ['terbayar', 'belumbayar', 'denda'])->default('belumbayar');
                       $table->foreign('id_pinjaman')->references('id')->on('pinjamen')->onDelete('cascade');
                       $table->foreign('id_anggota')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cicilans');
    }
};
