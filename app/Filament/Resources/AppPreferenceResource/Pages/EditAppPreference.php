<?php

namespace App\Filament\Resources\AppPreferenceResource\Pages;

use App\Filament\Resources\AppPreferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppPreference extends EditRecord
{
    protected static string $resource = AppPreferenceResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
