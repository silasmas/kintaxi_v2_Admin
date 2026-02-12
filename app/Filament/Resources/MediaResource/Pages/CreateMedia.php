<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $path = $data['path'] ?? null;
        if (is_array($path)) {
            $path = $path[0] ?? null;
        }
        $data['path'] = is_string($path) ? $path : ($data['path'] ?? '');
        $data['disk'] = env('FILAMENT_FILESYSTEM_DISK', 's3_media');
        return $data;
    }
}
