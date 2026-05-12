<?php

namespace App\Filament\Resources\LoyaltyRedemptionHistoryResource\Pages;

use App\Filament\Resources\LoyaltyRedemptionHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyRedemptionHistory extends EditRecord
{
    protected static string $resource = LoyaltyRedemptionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
