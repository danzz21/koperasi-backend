<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Audit trail otomatis untuk seluruh perubahan model koperasi.
        foreach ([
            \App\Models\User::class,
            \App\Models\Simpanan::class,
            \App\Models\Pinjaman::class,
            \App\Models\Cicilan::class,
            \App\Models\Pembayaran::class,
            \App\Models\Transaksi::class,
            \App\Models\PpobTransaksi::class,
            \App\Models\Akad::class,
        ] as $model) {
            $model::observe(\App\Observers\AuditObserver::class);
        }
    }
}
