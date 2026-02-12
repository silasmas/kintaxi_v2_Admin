<?php

namespace App\Filament\Resources\PasswordResetResource\Pages;

use App\Filament\Resources\PasswordResetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPasswordReset extends ViewRecord
{
    protected static string $resource = PasswordResetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
