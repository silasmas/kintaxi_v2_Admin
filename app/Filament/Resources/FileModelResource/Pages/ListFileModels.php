<?php

namespace App\Filament\Resources\FileModelResource\Pages;

use App\Filament\Resources\FileModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFileModels extends ListRecords
{
    protected static string $resource = FileModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
