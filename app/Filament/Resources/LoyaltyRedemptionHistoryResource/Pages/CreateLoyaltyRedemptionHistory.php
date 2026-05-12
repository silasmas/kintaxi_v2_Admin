<?php

namespace App\Filament\Resources\LoyaltyRedemptionHistoryResource\Pages;

use App\Filament\Resources\LoyaltyRedemptionHistoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLoyaltyRedemptionHistory extends CreateRecord
{
    protected static string $resource = LoyaltyRedemptionHistoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
