<?php

namespace App\Filament\Resources\LoyaltyRedemptionHistoryResource\Pages;

use App\Filament\Resources\LoyaltyRedemptionHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLoyaltyRedemptionHistory extends ViewRecord
{
    protected static string $resource = LoyaltyRedemptionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
