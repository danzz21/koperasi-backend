<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\DashboardPpob;
use App\Filament\Pages\LaporanAnalisis;
use App\Filament\Pages\PendingAnggota;
use App\Filament\Pages\PendingPembayaran;
use App\Filament\Pages\PendingPinjaman;
use App\Filament\Pages\PendingSimpananPokok;
use App\Filament\Pages\PendingSimpananWajib;
use App\Filament\Pages\PendingSukarela;
use App\Filament\Pages\PendingWithdrawalRequests;
use App\Filament\Pages\TransaksiUmum;
use App\Filament\Resources\Akads\AkadResource;
use App\Filament\Resources\Cicilans\CicilanResource;
use App\Filament\Resources\Pembayarans\PembayaranResource;
use App\Filament\Resources\Pinjamen\PinjamanResource;
use App\Filament\Resources\PpobTransaksis\PpobTransaksiResource;
use App\Filament\Resources\Simpanans\SimpananResource;
use App\Filament\Resources\Transaksis\TransaksiResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors(['primary' => Color::Emerald])
            ->darkMode(false)
            ->assets([
                Css::make('admin-theme', asset('css/admin-theme.css')),
                Css::make('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'),
                Js::make('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js'),
            ])
            ->brandName('Koperasi K-Samara')
            ->brandLogo(null)
            ->maxContentWidth(Width::Full)
            ->pages([
                Dashboard::class,
                TransaksiUmum::class,
                DashboardPpob::class,
                LaporanAnalisis::class,
                PendingAnggota::class,
                PendingSukarela::class,
                PendingSimpananPokok::class,
                PendingSimpananWajib::class,
                PendingPinjaman::class,
                PendingPembayaran::class,
                PendingWithdrawalRequests::class,
            ])
            ->resources([
                UserResource::class,
                SimpananResource::class,
                PinjamanResource::class,
                CicilanResource::class,
                PembayaranResource::class,
                TransaksiResource::class,
                PpobTransaksiResource::class,
                AkadResource::class,
            ])
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
