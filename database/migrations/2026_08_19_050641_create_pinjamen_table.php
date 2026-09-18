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
        Schema::create('pinjamen', function (Blueprint $table) {
            $table->id();
                       $table->bigInteger('id_anggota')->unsigned();
                       $table->enum('tipe', ['qard', 'murabahah', 'mudharabah'])->default('qard');
                       $table->decimal('nominal', 15, 2);
                       $table->decimal('sisa_pinjaman', 15, 2);
                       $table->integer('tenor')->comment('dalam bulan');
                       $table->decimal('bunga_per_bulan', 5, 2)->nullable();
                       $table->decimal('angsuran', 15, 2);
                       $table->integer('cicilan_ke')->default(0);
                       $table->enum('status', ['aktif', 'lunas', 'ditolak'])->default('aktif');
                       $table->date('tgl_persetujuan')->nullable();
                       $table->date('tgl_cair')->nullable();
                       $table->text('keterangan')->nullable();
                       $table->foreign('id_anggota')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjamen');
    }
};
