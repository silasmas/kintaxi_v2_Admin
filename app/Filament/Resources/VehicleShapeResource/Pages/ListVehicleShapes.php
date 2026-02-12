<?php

namespace App\Filament\Resources\VehicleShapeResource\Pages;

use App\Filament\Resources\VehicleShapeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVehicleShapes extends ListRecords
{
    protected static string $resource = VehicleShapeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
