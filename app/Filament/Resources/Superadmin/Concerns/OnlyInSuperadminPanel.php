<?php

namespace App\Filament\Resources\Superadmin\Concerns;

/**
 * Resource di namespace Superadmin hanya boleh muncul & diakses
 * pada panel "superadmin" oleh user dengan role superadmin.
 *
 * AdminPanelProvider memakai discoverResources() yang bersifat rekursif,
 * sehingga tanpa guard ini resource superadmin akan ikut tampil di /admin.
 */
trait OnlyInSuperadminPanel
{
    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'superadmin'
            && auth()->user()?->role === 'superadmin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'superadmin';
    }
}
