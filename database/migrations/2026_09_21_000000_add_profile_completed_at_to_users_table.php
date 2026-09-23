<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'profile_completed_at')) {
                $table->timestamp('profile_completed_at')->nullable()->after('status');
            }
        });

        // Backfill: user lama yang datanya sudah lengkap tidak perlu onboarding.
        // Akun Google otomatis memakai placeholder KTP ("G...") dan nomor HP "-",
        // sehingga akun seperti itu tetap dibiarkan null → wajib melengkapi data.
        DB::table('users')
            ->whereNull('profile_completed_at')
            ->where(function ($query) {
                $query->where('role', '!=', 'anggota')
                    ->orWhere(function ($inner) {
                        $inner->where('nomor_ktp', 'not like', 'G%')
                            ->where('nomor_hp', '!=', '-')
                            ->whereNotNull('nomor_hp');
                    });
            })
            ->update(['profile_completed_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_completed_at');
        });
    }
};
