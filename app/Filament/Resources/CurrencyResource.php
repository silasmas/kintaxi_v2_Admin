<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Models\Currency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Devises';

    protected static ?string $modelLabel = 'Devise';

    protected static ?string $pluralModelLabel = 'Devises';

    protected static ?string $navigationGroup = 'Référentiels';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('currency_acronym')
                    ->label('Code'),
                TextEntry::make('currency_name')
                    ->label('Nom'),
                TextEntry::make('rating')
                    ->label('Taux USD'),
            ])
            ->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Devise')
                    ->schema([
                        Forms\Components\TextInput::make('currency_name')
                            ->label('Nom')
                            ->maxLength(45),
                        Forms\Components\TextInput::make('currency_acronym')
                            ->label('Acronyme (ex: USD, CDF)')
                            ->required()
                            ->maxLength(45),
                        Forms\Components\TextInput::make('rating')
                            ->label('Taux (vs USD)')
                            ->numeric()
                            ->step(0.01),
                        Forms\Components\TextInput::make('icon')
                            ->label('Icône')
                            ->maxLength(45),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('currency_acronym')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency_name')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Taux USD')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
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
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'view' => Pages\ViewCurrency::route('/{record}'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
