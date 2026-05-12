<?php

namespace App\Filament\Resources\LoyaltyRedemptionHistoryResource\Pages;

use App\Filament\Resources\LoyaltyRedemptionHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyRedemptionHistories extends ListRecords
{
    protected static string $resource = LoyaltyRedemptionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
