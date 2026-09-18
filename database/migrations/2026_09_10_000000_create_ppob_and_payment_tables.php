<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ppob_transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->foreignId('id_anggota')->constrained('users')->cascadeOnDelete();
            $table->string('jenis_produk');
            $table->string('provider');
            $table->string('nomor_tujuan');
            $table->decimal('nominal', 15, 2);
            $table->decimal('harga', 15, 2);
            $table->string('kode_produk');
            $table->string('nama_produk');
            $table->string('status')->default('pending');
            $table->string('ref_id')->nullable();
            $table->string('payment_ref')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_gateway_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_anggota')->constrained('users')->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->string('jenis_pembayaran');
            $table->string('ref_id')->nullable();
            $table->decimal('nominal', 15, 2);
            $table->decimal('biaya_admin', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2);
            $table->string('payment_method');
            $table->string('payment_code')->nullable();
            $table->dateTime('payment_expired')->nullable();
            $table->string('status')->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_transaksi');
        Schema::dropIfExists('ppob_transaksi');
    }
};
