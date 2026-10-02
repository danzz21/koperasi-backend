<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melengkapi E-Wallet:topup dari saldo simpanan, instruksi/VA manual,
 * dan integrasi payment gateway (Midtrans) dengan webhook.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ewallet_transactions', function (Blueprint $table) {
            // 'pindah' = mutasi dari saldo simpanan anggota ke e-wallet
            $table->enum('tipe', ['topup', 'bayar', 'pindah'])->change();

            $table->string('gateway')->nullable()->after('metode');
            $table->string('kode_va')->nullable()->after('gateway');
            $table->text('payment_url')->nullable()->after('kode_va');
            $table->string('bukti_transfer')->nullable()->after('payment_url');
            $table->datetime('expired_at')->nullable()->after('bukti_transfer');

            $table->index('gateway');
        });
    }

    public function down(): void
    {
        Schema::table('ewallet_transactions', function (Blueprint $table) {
            $table->dropIndex(['gateway']);
            $table->dropColumn([
                'gateway', 'kode_va', 'payment_url', 'bukti_transfer', 'expired_at',
            ]);
            $table->enum('tipe', ['topup', 'bayar'])->change();
        });
    }
};
