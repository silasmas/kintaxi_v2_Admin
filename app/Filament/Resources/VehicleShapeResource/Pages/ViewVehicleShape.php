<?php

namespace App\Filament\Resources\VehicleShapeResource\Pages;

use App\Filament\Resources\VehicleShapeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicleShape extends ViewRecord
{
    protected static string $resource = VehicleShapeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
