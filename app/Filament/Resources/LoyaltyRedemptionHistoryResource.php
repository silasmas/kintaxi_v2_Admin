<?php

namespace App\Filament\Resources;

use App\Enums\LoyaltyRedemptionCurrency;
use App\Enums\LoyaltyTransactionType;
use App\Filament\Resources\LoyaltyRedemptionHistoryResource\Pages;
use App\Models\LoyaltyHistory;
use App\Models\LoyaltyRedemptionHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyRedemptionHistoryResource extends Resource
{
    protected static ?string $model = LoyaltyRedemptionHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Conversions points → wallet';

    protected static ?string $modelLabel = 'Conversion fidélité';

    protected static ?string $pluralModelLabel = 'Conversions fidélité';

    protected static ?string $navigationGroup = 'Fidélité';

    protected static ?int $navigationSort = 2;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Conversion')->schema([
                Infolists\Components\TextEntry::make('id')->label('ID'),
                Infolists\Components\TextEntry::make('user.email')->label('Utilisateur'),
                Infolists\Components\TextEntry::make('points_redeemed')->label('Points déduits'),
                Infolists\Components\TextEntry::make('conversion_rate_applied')->label('Taux appliqué (pts / unité)'),
                Infolists\Components\TextEntry::make('amount_usd')
                    ->label('Montant (Dollar US)')
                    ->formatStateUsing(fn (?string $state): string => $state !== null
                        ? number_format((float) $state, 2, ',', ' ').' Dollar US'
                        : '—'),
                Infolists\Components\TextEntry::make('amount_cdf')->label('Montant CDF')->money('CDF'),
                Infolists\Components\TextEntry::make('daily_exchange_rate')->label('Taux USD/CDF'),
                Infolists\Components\TextEntry::make('wallet_balance_before')->label('Wallet avant')->money('CDF'),
                Infolists\Components\TextEntry::make('wallet_balance_after')->label('Wallet après')->money('CDF'),
                Infolists\Components\TextEntry::make('currency_used')
                    ->label('Devise cible')
                    ->formatStateUsing(fn (LoyaltyRedemptionCurrency $state): string => $state->label()),
                Infolists\Components\TextEntry::make('loyaltyMovement.id')
                    ->label('Mouvement fidélité lié')
                    ->formatStateUsing(fn (?int $state): string => $state ? '#'.$state : '—'),
                Infolists\Components\TextEntry::make('creator.email')->label('Créé par'),
                Infolists\Components\TextEntry::make('created_at')->label('Date')->dateTime('d/m/Y H:i'),
            ])->columns(2),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Conversion points → portefeuille')
                ->description('Liez un mouvement de type « Conversion » dans l’historique fidélité. À la création, le solde wallet utilisateur est mis à jour sur « Wallet après ».')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Utilisateur')
                        ->relationship('user', 'email')
                        ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->phone ?? '#'.$record->id))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live(),
                    Forms\Components\Select::make('reference_loyalty_id')
                        ->label('Mouvement loyalty_history associé')
                        ->options(function (Get $get): array {
                            $userId = $get('user_id');
                            if (! $userId) {
                                return [];
                            }

                            return LoyaltyHistory::query()
                                ->where('user_id', $userId)
                                ->orderByDesc('id')
                                ->limit(200)
                                ->get()
                                ->mapWithKeys(function (LoyaltyHistory $row): array {
                                    $type = $row->transaction_type instanceof LoyaltyTransactionType
                                        ? $row->transaction_type->label()
                                        : (string) $row->transaction_type;
                                    $label = sprintf(
                                        '#%d · %s · %d pts · %s',
                                        $row->id,
                                        $type,
                                        $row->points_earned,
                                        $row->created_at?->format('d/m/Y H:i') ?? ''
                                    );

                                    return [$row->id => $label];
                                })
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Choisissez d’abord l’utilisateur. Préférez un mouvement de type conversion / débit.'),
                    Forms\Components\TextInput::make('points_redeemed')
                        ->label('Points déduits')
                        ->required()
                        ->integer()
                        ->minValue(1),
                    Forms\Components\TextInput::make('conversion_rate_applied')
                        ->label('Taux de conversion (points vers Dollar)')
                        ->helperText('Ex. 100 → 100 points pour 1 unité Dollar US.')
                        ->required()
                        ->integer()
                        ->minValue(1),
                    Forms\Components\TextInput::make('amount_usd')
                        ->label('Montant net (Dollar US)')
                        ->suffix(' Dollar US')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('amount_cdf')
                        ->label('Montant CDF crédité')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('daily_exchange_rate')
                        ->label('Taux USD/CDF du jour')
                        ->numeric()
                        ->required()
                        ->step(0.0001),
                    Forms\Components\TextInput::make('wallet_balance_before')
                        ->label('Solde wallet avant')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('wallet_balance_after')
                        ->label('Solde wallet après')
                        ->numeric()
                        ->required()
                        ->helperText('Doit refléter le crédit CDF (ou logique métier équivalente).'),
                    Forms\Components\Select::make('currency_used')
                        ->label('Devise principale du crédit')
                        ->options(collect(LoyaltyRedemptionCurrency::cases())->mapWithKeys(
                            fn (LoyaltyRedemptionCurrency $c) => [$c->value => $c->label()]
                        ))
                        ->default(LoyaltyRedemptionCurrency::Usd->value)
                        ->required(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Utilisateur')->searchable(),
                Tables\Columns\TextColumn::make('points_redeemed')->label('Pts')->sortable(),
                Tables\Columns\TextColumn::make('amount_cdf')->label('CDF')->money('CDF')->sortable(),
                Tables\Columns\TextColumn::make('amount_usd')
                    ->label('Dollar US')
                    ->formatStateUsing(fn (?string $state): string => $state !== null
                        ? number_format((float) $state, 2, ',', ' ').' Dollar US'
                        : '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('currency_used')
                    ->label('Devise')
                    ->badge()
                    ->formatStateUsing(fn (LoyaltyRedemptionCurrency $state): string => $state->label()),
                Tables\Columns\TextColumn::make('reference_loyalty_id')->label('Ref. loyalty'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('currency_used')
                    ->label('Devise')
                    ->options(collect(LoyaltyRedemptionCurrency::cases())->mapWithKeys(
                        fn (LoyaltyRedemptionCurrency $c) => [$c->value => $c->label()]
                    )),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyRedemptionHistories::route('/'),
            'create' => Pages\CreateLoyaltyRedemptionHistory::route('/create'),
            'view' => Pages\ViewLoyaltyRedemptionHistory::route('/{record}'),
            'edit' => Pages\EditLoyaltyRedemptionHistory::route('/{record}/edit'),
        ];
    }
}
