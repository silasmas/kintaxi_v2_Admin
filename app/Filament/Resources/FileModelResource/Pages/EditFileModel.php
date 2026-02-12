<?php

namespace App\Filament\Resources\FileModelResource\Pages;

use App\Filament\Resources\FileModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFileModel extends EditRecord
{
    protected static string $resource = FileModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }
}
