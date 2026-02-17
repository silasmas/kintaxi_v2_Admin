<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ForbiddenPage extends Page
{
    protected static string $view = 'filament.pages.forbidden';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'forbidden';

    public function getTitle(): string
    {
        return 'Accès refusé';
    }

    public function getHeading(): string
    {
        return 'Accès refusé';
    }
}
