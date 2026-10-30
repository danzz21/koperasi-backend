<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_anggota')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_simpanan', 15, 2);
            $table->decimal('total_kewajiban', 15, 2)->default(0);
            $table->decimal('biaya_admin', 15, 2)->default(0);
            $table->decimal('jumlah_pencairan', 15, 2);
            $table->string('status')->default('pending');
            $table->string('nomor_rekening')->nullable();
            $table->string('jenis_bank')->nullable();
            $table->string('atasnama_rekening')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamp('dicairkan_pada')->nullable();
            $table->timestamps();

            $table->index(['id_anggota', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
