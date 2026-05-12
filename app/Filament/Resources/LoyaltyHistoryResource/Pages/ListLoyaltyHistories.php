<?php

namespace App\Filament\Resources\LoyaltyHistoryResource\Pages;

use App\Filament\Resources\LoyaltyHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyHistories extends ListRecords
{
    protected static string $resource = LoyaltyHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
