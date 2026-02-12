<?php

namespace App\Filament\Resources\FileModelResource\Pages;

use App\Filament\Resources\FileModelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFileModel extends CreateRecord
{
    protected static string $resource = FileModelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
