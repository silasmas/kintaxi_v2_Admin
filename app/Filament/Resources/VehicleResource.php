<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Véhicules';

    protected static ?string $modelLabel = 'Véhicule';

    protected static ?string $pluralModelLabel = 'Véhicules';

    protected static ?string $navigationGroup = 'Véhicules';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('registration_number')->label('Plaque'),
            TextEntry::make('mark')->label('Marque'),
            TextEntry::make('model')->label('Modèle'),
            TextEntry::make('owner.email')->label('Propriétaire'),
            TextEntry::make('category.category_name')->label('Catégorie'),
            TextEntry::make('nb_places')->label('Places'),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Statut & Propriétaire')->schema([
                    Forms\Components\Select::make('status_id')
                        ->label('Statut')
                        ->relationship('status', 'status_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->status_name ?? $record->id ?? '—'))
                        ->required()
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('user_id')
                        ->label('Propriétaire')
                        ->relationship('owner', 'email')
                        ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->phone ?? $record->id ?? '—'))
                        ->searchable()
                        ->preload()
                        ->getSearchResultsUsing(fn (string $search) => \App\Models\User::query()
                            ->where('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->limit(50)->pluck('email', 'id')),
                ])->columns(2),
                Forms\Components\Section::make('Caractéristiques')->schema([
                    Forms\Components\TextInput::make('mark')->label('Marque')->maxLength(255),
                    Forms\Components\TextInput::make('model')->label('Modèle')->maxLength(255),
                    Forms\Components\TextInput::make('color')->label('Couleur')->maxLength(45),
                    Forms\Components\TextInput::make('registration_number')->label('Plaque')->maxLength(255),
                    Forms\Components\TextInput::make('vin_number')->label('N° châssis')->maxLength(255),
                    Forms\Components\TextInput::make('manufacture_year')->label('Année')->numeric()->minValue(1900)->maxValue(2100),
                    Forms\Components\TextInput::make('fuel_type')->label('Carburant')->maxLength(255),
                    Forms\Components\TextInput::make('cylinder_capacity')->label('Cylindrée')->numeric()->step(0.01),
                    Forms\Components\TextInput::make('engine_power')->label('Puissance moteur')->numeric()->step(0.01),
                    Forms\Components\Select::make('shape_id')
                        ->label('Forme')
                        ->relationship('shape', 'shape_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->shape_name ?? $record->id ?? '—'))
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('category_id')
                        ->label('Catégorie')
                        ->relationship('category', 'category_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->category_name ?? $record->id ?? '—'))
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('nb_places')
                        ->label('Nombre de places (hors chauffeur)')
                        ->required()
                        ->numeric()
                        ->minValue(1),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')->label('Plaque')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('mark')->label('Marque')->searchable(),
                Tables\Columns\TextColumn::make('model')->label('Modèle')->searchable(),
                Tables\Columns\TextColumn::make('owner.email')->label('Propriétaire'),
                Tables\Columns\TextColumn::make('category.category_name')->label('Catégorie')->badge(),
                Tables\Columns\TextColumn::make('nb_places')->label('Places')->sortable(),
                Tables\Columns\TextColumn::make('status.status_name')->label('Statut')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'category_name')->label('Catégorie'),
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'view' => Pages\ViewVehicle::route('/{record}'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
