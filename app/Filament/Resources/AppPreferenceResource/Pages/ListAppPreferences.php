<?php

namespace App\Filament\Resources\AppPreferenceResource\Pages;

use App\Filament\Pages\ManageAppPreferences;
use App\Filament\Resources\AppPreferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppPreferences extends ListRecords
{
    protected static string $resource = AppPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('retour_tableau')
                ->label('Retour au tableau des réglages')
                ->url(ManageAppPreferences::getUrl())
                ->icon('heroicon-o-table-cells')
                ->color('gray'),
            Actions\CreateAction::make(),
        ];
    }
}
