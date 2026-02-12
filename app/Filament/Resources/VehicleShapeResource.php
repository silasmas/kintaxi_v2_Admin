<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleShapeResource\Pages;
use App\Models\VehicleShape;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleShapeResource extends Resource
{
    protected static ?string $model = VehicleShape::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Formes véhicules';

    protected static ?string $modelLabel = 'Forme véhicule';

    protected static ?string $pluralModelLabel = 'Formes véhicules';

    protected static ?string $navigationGroup = 'Véhicules';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('shape_name')->label('Nom'),
            TextEntry::make('shape_description')->label('Description')->columnSpanFull(),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Forme véhicule')->schema([
                Forms\Components\TextInput::make('shape_name')
                    ->label('Nom de la forme')
                    ->maxLength(255),
                Forms\Components\Textarea::make('shape_description')
                    ->label('Description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('photo')
                    ->label('URL photo')
                    ->url()
                    ->maxLength(1000),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shape_name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('shape_description')->label('Description')->limit(40),
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
            'index' => Pages\ListVehicleShapes::route('/'),
            'create' => Pages\CreateVehicleShape::route('/create'),
            'view' => Pages\ViewVehicleShape::route('/{record}'),
            'edit' => Pages\EditVehicleShape::route('/{record}/edit'),
        ];
    }
}
