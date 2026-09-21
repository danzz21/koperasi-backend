<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
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
                Js::make('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js'),
            ])
            ->brandName('Koperasi K-Samara')
            ->brandLogo(null)
            ->maxContentWidth(Width::Full)
            ->pages([
                Dashboard::class,
                \App\Filament\Pages\LaporanAnalisis::class,
            ])
            ->resources([
                \App\Filament\Resources\Users\UserResource::class,
                \App\Filament\Resources\Simpanans\SimpananResource::class,
                \App\Filament\Resources\Pinjamen\PinjamanResource::class,
                \App\Filament\Resources\Cicilans\CicilanResource::class,
                \App\Filament\Resources\Pembayarans\PembayaranResource::class,
                \App\Filament\Resources\Transaksis\TransaksiResource::class,
                \App\Filament\Resources\PpobTransaksis\PpobTransaksiResource::class,
                \App\Filament\Resources\Akads\AkadResource::class,
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
