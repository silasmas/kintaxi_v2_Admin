<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Pays';

    protected static ?string $modelLabel = 'Pays';

    protected static ?string $pluralModelLabel = 'Pays';

    protected static ?string $navigationGroup = 'Référentiels';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('code')->label('Code'),
                TextEntry::make('name_fr')->label('Nom (FR)'),
                TextEntry::make('name_en')->label('Nom (EN)'),
                TextEntry::make('code_tel')->label('Code téléphone'),
            ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pays')->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Code')
                    ->maxLength(2),
                Forms\Components\TextInput::make('name_en')
                    ->label('Nom (EN)')
                    ->required()
                    ->maxLength(80),
                Forms\Components\TextInput::make('name_fr')
                    ->label('Nom (FR)')
                    ->required()
                    ->maxLength(80),
                Forms\Components\TextInput::make('code_tel')
                    ->label('Code téléphone')
                    ->maxLength(8),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Code')->searchable(),
                Tables\Columns\TextColumn::make('name_fr')->label('Nom FR')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name_en')->label('Nom EN')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code_tel')->label('Code tél.'),
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'view' => Pages\ViewCountry::route('/{record}'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
