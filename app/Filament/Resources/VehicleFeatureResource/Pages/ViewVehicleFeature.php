<?php

namespace App\Filament\Resources\VehicleFeatureResource\Pages;

use App\Filament\Resources\VehicleFeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicleFeature extends ViewRecord
{
    protected static string $resource = VehicleFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
