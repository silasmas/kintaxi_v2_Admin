<?php

namespace App\Filament\Resources\LoyaltyHistoryResource\Pages;

use App\Filament\Resources\LoyaltyHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyHistory extends EditRecord
{
    protected static string $resource = LoyaltyHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
