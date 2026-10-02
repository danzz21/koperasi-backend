<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * E-Wallet Koperasi — saldo terpisah dari simpanan, khusus untuk bayar PPOB
 * dan bisa di-topup (menunggu verifikasi admin).
 *
 * - ewallets             : saldo tiap anggota
 * - ewallet_transactions : riwayat lengkap (topup pending/sukses, pembayaran)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ewallets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_anggota')->unsigned()->unique();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_anggota')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('ewallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_anggota')->unsigned();
            $table->enum('tipe', ['topup', 'bayar']);
            $table->enum('status', ['pending', 'sukses', 'gagal']);
            $table->decimal('nominal', 15, 2);
            $table->string('metode')->nullable();
            $table->string('ref_id')->nullable()->unique();
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_anggota')->references('id')->on('users')->onDelete('cascade');
            $table->index(['id_anggota', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ewallet_transactions');
        Schema::dropIfExists('ewallets');
    }
};
