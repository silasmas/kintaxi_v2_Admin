<?php

namespace App\Filament\Resources\VehicleFeatureResource\Pages;

use App\Filament\Resources\VehicleFeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVehicleFeatures extends ListRecords
{
    protected static string $resource = VehicleFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
