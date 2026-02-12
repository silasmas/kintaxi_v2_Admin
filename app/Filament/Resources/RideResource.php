<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideResource\Pages;
use App\Models\Ride;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RideResource extends Resource
{
    protected static ?string $model = Ride::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Courses';

    protected static ?string $modelLabel = 'Course';

    protected static ?string $pluralModelLabel = 'Courses';

    protected static ?string $navigationGroup = 'Courses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Course')->schema([
                Forms\Components\Select::make('ride_status')
                    ->label('Statut')
                    ->options([
                        'requested' => 'Demandée',
                        'accepted' => 'Acceptée',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminée',
                        'canceled' => 'Annulée',
                    ])
                    ->required(),
                Forms\Components\Select::make('vehicle_category_id')
                    ->label('Catégorie véhicule')
                    ->relationship('vehicleCategory', 'category_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->category_name ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Véhicule')
                    ->relationship('vehicle', 'registration_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->registration_number ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('passenger_id')
                    ->label('Passager')
                    ->relationship('passenger', 'email')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->phone ?? $record->id ?? '—'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('driver_id')
                    ->label('Chauffeur')
                    ->relationship('driver', 'email')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->phone ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('distance')->label('Distance (km)')->required()->numeric()->step(0.01),
                Forms\Components\TextInput::make('cost')->label('Coût')->numeric()->step(0.01),
                Forms\Components\TextInput::make('estimated_cost')->label('Coût estimé')->numeric()->step(0.01),
                Forms\Components\Select::make('payment_method')
                    ->label('Méthode de paiement')
                    ->options([
                        'cash' => 'Espèces',
                        'kintaxi-wallet' => 'Portefeuille Kintaxi',
                        'mobile-money' => 'Mobile Money',
                        'card' => 'Carte',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('paid')->label('Payé'),
                Forms\Components\TextInput::make('commission')->label('Commission (%)')->numeric()->default(15)->step(0.01),
                Forms\Components\Toggle::make('is_scheduled')->label('Planifiée'),
                Forms\Components\DateTimePicker::make('scheduled_time')->label('Date/heure planifiée'),
                Forms\Components\Select::make('canceled_by')
                    ->label('Annulée par')
                    ->options(['passenger' => 'Passager', 'driver' => 'Chauffeur']),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('ride_status')->label('Statut')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'requested' => 'Demandée',
                    'accepted' => 'Acceptée',
                    'in_progress' => 'En cours',
                    'completed' => 'Terminée',
                    'canceled' => 'Annulée',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('passenger.email')->label('Passager')->searchable(),
                Tables\Columns\TextColumn::make('driver.email')->label('Chauffeur'),
                Tables\Columns\TextColumn::make('distance')->label('Distance')->suffix(' km')->sortable(),
                Tables\Columns\TextColumn::make('cost')->label('Coût')->money('CDF')->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->label('Paiement')->badge(),
                Tables\Columns\IconColumn::make('paid')->label('Payé')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('ride_status')->options([
                    'requested' => 'Demandée',
                    'accepted' => 'Acceptée',
                    'in_progress' => 'En cours',
                    'completed' => 'Terminée',
                    'canceled' => 'Annulée',
                ]),
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
            'index' => Pages\ListRides::route('/'),
            'create' => Pages\CreateRide::route('/create'),
            'view' => Pages\ViewRide::route('/{record}'),
            'edit' => Pages\EditRide::route('/{record}/edit'),
        ];
    }
}
