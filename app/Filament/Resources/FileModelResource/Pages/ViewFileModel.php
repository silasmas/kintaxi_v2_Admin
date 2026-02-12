<?php

namespace App\Filament\Resources\FileModelResource\Pages;

use App\Filament\Resources\FileModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFileModel extends ViewRecord
{
    protected static string $resource = FileModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
