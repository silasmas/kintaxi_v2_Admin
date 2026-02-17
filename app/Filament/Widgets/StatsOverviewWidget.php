<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\AppNotification;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Document;
use App\Models\FileModel;
use App\Models\Payment;
use App\Models\Ride;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverviewWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Résumé de la plateforme';

    protected ?string $description = 'Vue d\'ensemble des principales données KinTaxi.';

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $baseUrl = '/admin';

        return [
            Stat::make('Utilisateurs', Number::format(User::count()))
                ->description('Comptes enregistrés')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->url("{$baseUrl}/users", false),

            Stat::make('Véhicules', Number::format(Vehicle::count()))
                ->description('Véhicules enregistrés')
                ->descriptionIcon('heroicon-m-truck')
                ->color('gray')
                ->url("{$baseUrl}/vehicles", false),

            Stat::make('Courses', Number::format(Ride::count()))
                ->description('Total des courses')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('success')
                ->url("{$baseUrl}/rides", false),

            Stat::make('Transactions', Number::format(Transaction::count()))
                ->description('Mouvements financiers')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('info')
                ->url("{$baseUrl}/transactions", false),

            Stat::make('Paiements', Number::format(Payment::count()))
                ->description('Paiements effectués')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success')
                ->url("{$baseUrl}/payments", false),

            Stat::make('Avis', Number::format(Review::count()))
                ->description('Avis clients')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning')
                ->url("{$baseUrl}/reviews", false),

            Stat::make('Documents', Number::format(Document::count()))
                ->description('Documents uploadés')
                ->descriptionIcon('heroicon-m-document-text')
                ->url("{$baseUrl}/documents", false),

            Stat::make('Fichiers', Number::format(FileModel::count()))
                ->description('Fichiers stockés')
                ->descriptionIcon('heroicon-m-folder')
                ->url("{$baseUrl}/file-models", false),

            Stat::make('Notifications', Number::format(AppNotification::count()))
                ->description('Notifications envoyées')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->url("{$baseUrl}/app-notifications", false),

            Stat::make('Pays', Number::format(Country::count()))
                ->description('Pays configurés')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->url("{$baseUrl}/countries", false),

            Stat::make('Catégories véhicules', Number::format(VehicleCategory::count()))
                ->description('Types de véhicules')
                ->descriptionIcon('heroicon-m-square-2-stack')
                ->url("{$baseUrl}/vehicle-categories", false),

            Stat::make('Devises', Number::format(Currency::count()))
                ->description('Devises actives')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->url("{$baseUrl}/currencies", false),
        ];
    }
}
