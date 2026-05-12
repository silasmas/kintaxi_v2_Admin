<?php

namespace App\Filament\Resources\AppPreferenceResource\Pages;

use App\Filament\Resources\AppPreferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppPreference extends ViewRecord
{
    protected static string $resource = AppPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
