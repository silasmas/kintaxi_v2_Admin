<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\LoyaltyTransactionType;
use App\Filament\Resources\LoyaltyHistoryResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'loyaltyHistories';

    protected static ?string $title = 'Historique fidélité';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (LoyaltyTransactionType $state): string => $state->label())
                    ->color(fn (LoyaltyTransactionType $state): string => $state->color()),
                Tables\Columns\TextColumn::make('points_earned')->label('Δ pts'),
                Tables\Columns\TextColumn::make('points_after_transaction')->label('Solde après'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('voir')
                    ->label('Voir')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn ($record): string => LoyaltyHistoryResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
