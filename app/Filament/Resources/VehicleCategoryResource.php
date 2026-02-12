<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleCategoryResource\Pages;
use App\Models\VehicleCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleCategoryResource extends Resource
{
    protected static ?string $model = VehicleCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationLabel = 'Catégories véhicules';

    protected static ?string $modelLabel = 'Catégorie véhicule';

    protected static ?string $pluralModelLabel = 'Catégories véhicules';

    protected static ?string $navigationGroup = 'Véhicules';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('category_name')->label('Nom'),
            TextEntry::make('status.status_name')->label('Statut'),
            TextEntry::make('category_description')->label('Description')->columnSpanFull(),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Catégorie véhicule')->schema([
                Forms\Components\Select::make('status_id')
                    ->label('Statut')
                    ->relationship('status', 'status_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->status_name ?? $record->id ?? '—'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('category_name')
                    ->label('Nom de la catégorie')
                    ->maxLength(255),
                Forms\Components\Textarea::make('category_description')
                    ->label('Description')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('image')
                    ->label('URL image / icône')
                    ->url()
                    ->maxLength(1000),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category_name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status.status_name')->label('Statut')->badge(),
                Tables\Columns\TextColumn::make('vehicles_count')->label('Véhicules')->counts('vehicles'),
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
            'index' => Pages\ListVehicleCategories::route('/'),
            'create' => Pages\CreateVehicleCategory::route('/create'),
            'view' => Pages\ViewVehicleCategory::route('/{record}'),
            'edit' => Pages\EditVehicleCategory::route('/{record}/edit'),
        ];
    }
}
