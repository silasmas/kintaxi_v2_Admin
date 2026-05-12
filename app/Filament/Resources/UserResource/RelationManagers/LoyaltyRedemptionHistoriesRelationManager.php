<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\LoyaltyRedemptionCurrency;
use App\Filament\Resources\LoyaltyRedemptionHistoryResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyRedemptionHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'loyaltyRedemptionHistories';

    protected static ?string $title = 'Conversions fidélité';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('points_redeemed')->label('Pts'),
                Tables\Columns\TextColumn::make('amount_cdf')->label('CDF')->money('CDF'),
                Tables\Columns\TextColumn::make('amount_usd')->label('USD')->money('USD')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('currency_used')
                    ->label('Devise')
                    ->badge()
                    ->formatStateUsing(fn (LoyaltyRedemptionCurrency $state): string => $state->label()),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn ($record): string => LoyaltyRedemptionHistoryResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
