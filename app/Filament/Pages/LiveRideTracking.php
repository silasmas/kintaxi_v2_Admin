<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class LiveRideTracking extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static string $view = 'filament.pages.live-ride-tracking';

    protected static ?string $navigationLabel = 'Suivi temps réel';

    protected static ?string $title = 'Suivi des courses en temps réel';

    protected static ?string $navigationGroup = 'Courses';

    protected static ?int $navigationSort = 3;
}
