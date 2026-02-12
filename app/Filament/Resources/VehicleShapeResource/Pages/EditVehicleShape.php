<?php

namespace App\Filament\Resources\VehicleShapeResource\Pages;

use App\Filament\Resources\VehicleShapeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicleShape extends EditRecord
{
    protected static string $resource = VehicleShapeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
