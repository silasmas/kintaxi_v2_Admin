<?php

namespace App\Filament\Resources\AppPreferenceResource\Pages;

use App\Filament\Resources\AppPreferenceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppPreference extends CreateRecord
{
    protected static string $resource = AppPreferenceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
