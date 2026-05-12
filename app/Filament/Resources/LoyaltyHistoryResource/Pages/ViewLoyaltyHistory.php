<?php

namespace App\Filament\Resources\LoyaltyHistoryResource\Pages;

use App\Filament\Resources\LoyaltyHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLoyaltyHistory extends ViewRecord
{
    protected static string $resource = LoyaltyHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
