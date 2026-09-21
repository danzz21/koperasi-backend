<?php

namespace App\Providers\Filament;

use App\Filament\Pages\LaporanKeuangan;
use App\Filament\Pages\MonitorTransaksi;
use App\Filament\Pages\SuperadminDashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SuperadminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('superadmin')
            ->path('superadmin')
            ->login()
            ->colors(['primary' => Color::Purple, 'secondary' => Color::Violet])
            ->darkMode(false)
            ->brandName('Superadmin — K-Samara')
            ->brandLogo(null)
            ->maxContentWidth(Width::Full)
            ->assets([
                Js::make('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js'),
            ])
            ->pages([
                SuperadminDashboard::class,
                LaporanKeuangan::class,
                MonitorTransaksi::class,
            ])
            ->discoverResources(
                in: app_path('Filament/Resources/Superadmin'),
                for: 'App\\Filament\\Resources\\Superadmin',
            )
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
