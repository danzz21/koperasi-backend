<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->foreignId('id_cicilan')->nullable()->after('id_anggota')
                ->constrained('cicilans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_cicilan');
        });
    }
};
