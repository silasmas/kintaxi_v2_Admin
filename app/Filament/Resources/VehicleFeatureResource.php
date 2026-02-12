<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleFeatureResource\Pages;
use App\Models\VehicleFeature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleFeatureResource extends Resource
{
    protected static ?string $model = VehicleFeature::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Équipements véhicules';

    protected static ?string $modelLabel = 'Équipement véhicule';

    protected static ?string $pluralModelLabel = 'Équipements véhicules';

    protected static ?string $navigationGroup = 'Véhicules';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('vehicle.registration_number')->label('Véhicule'),
            TextEntry::make('is_clean')->label('Propre')->formatStateUsing(fn ($s) => $s ? 'Oui' : 'Non'),
            TextEntry::make('has_air_conditioning')->label('Climatisation')->formatStateUsing(fn ($s) => $s ? 'Oui' : 'Non'),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Équipement véhicule')->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Véhicule')
                    ->relationship('vehicle', 'registration_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->registration_number ?? $record->id ?? '—'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('is_clean')->label('Propre'),
                Forms\Components\Toggle::make('has_helmet')->label('Casque'),
                Forms\Components\Toggle::make('has_airbags')->label('Airbags'),
                Forms\Components\Toggle::make('has_seat_belt')->label('Ceinture de sécurité'),
                Forms\Components\Toggle::make('has_ergonomic_seat')->label('Siège ergonomique'),
                Forms\Components\Toggle::make('has_air_conditioning')->label('Climatisation'),
                Forms\Components\Toggle::make('has_soundproofing')->label('Isolation phonique'),
                Forms\Components\Toggle::make('has_sufficient_space')->label('Espace suffisant'),
                Forms\Components\Toggle::make('has_quality_equipment')->label('Équipement qualité'),
                Forms\Components\Toggle::make('has_on_board_technologies')->label('Technologies embarquées'),
                Forms\Components\Toggle::make('has_interior_lighting')->label('Éclairage intérieur'),
                Forms\Components\Toggle::make('has_practical_accessories')->label('Accessoires pratiques'),
                Forms\Components\Toggle::make('has_driving_assist_system')->label('Aide à la conduite'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.registration_number')->label('Véhicule')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_clean')->label('Propre')->boolean(),
                Tables\Columns\IconColumn::make('has_air_conditioning')->label('Clim')->boolean(),
                Tables\Columns\IconColumn::make('has_seat_belt')->label('Ceinture')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_id')->relationship('vehicle', 'registration_number')->label('Véhicule'),
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
            'index' => Pages\ListVehicleFeatures::route('/'),
            'create' => Pages\CreateVehicleFeature::route('/create'),
            'view' => Pages\ViewVehicleFeature::route('/{record}'),
            'edit' => Pages\EditVehicleFeature::route('/{record}/edit'),
        ];
    }
}
