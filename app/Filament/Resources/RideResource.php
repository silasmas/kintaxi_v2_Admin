<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideResource\Pages;
use App\Filament\Support\CurrencyFormatter;
use App\Filament\Support\EligibleDriverQueryHelper;
use App\Filament\Support\StatusColorHelper;
use App\Filament\Support\UserTimezoneHelper;
use App\Models\Ride;
use App\Models\User;
use App\Services\ScheduledRideDriverNotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RideResource extends Resource
{
    protected static ?string $model = Ride::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Courses';

    protected static ?string $modelLabel = 'Course';

    protected static ?string $pluralModelLabel = 'Courses';

    protected static ?string $navigationGroup = 'Courses';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Informations course')
                ->schema([
                    TextEntry::make('ride_status')
                        ->label('Statut')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => StatusColorHelper::rideStatusLabel($state))
                        ->color(fn (string $state): string => StatusColorHelper::rideStatusColor($state)),
                    ViewEntry::make('passenger')
                        ->label('Client')
                        ->state(fn (Ride $record): ?User => $record->passenger)
                        ->view('filament.infolists.entries.user-participant'),
                    ViewEntry::make('driver')
                        ->label('Chauffeur')
                        ->state(fn (Ride $record): ?User => $record->driver)
                        ->view('filament.infolists.entries.user-participant'),
                    TextEntry::make('distance')->label('Distance')->suffix(' km'),
                    CurrencyFormatter::configureMoneyEntry(
                        TextEntry::make('cost')->label('Coût')
                    ),
                    TextEntry::make('payment_method')
                        ->label('Paiement')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => StatusColorHelper::paymentMethodLabel($state))
                        ->color(fn (string $state): string => StatusColorHelper::paymentMethodColor($state)),
                    TextEntry::make('is_scheduled')
                        ->label('Planifiée')
                        ->formatStateUsing(fn (?bool $state): string => $state ? 'Oui' : 'Non'),
                    TextEntry::make('scheduled_time')
                        ->label('Heure planifiée')
                        ->formatStateUsing(fn ($state): string => UserTimezoneHelper::formatDateTime($state))
                        ->visible(fn (Ride $record): bool => (bool) $record->is_scheduled),
                    TextEntry::make('created_at')->label('Date')->dateTime('d/m/Y H:i'),
                ])->columns(2),
            Section::make('Trajet sur carte')
                ->schema([
                    ViewEntry::make('ride_map')
                        ->label('')
                        ->view('filament.infolists.entries.ride-route-map')
                        ->columnSpanFull(),
                ]),
            Section::make('Photo & évaluation')
                ->schema([
                    ViewEntry::make('ride_extras')
                        ->label('')
                        ->view('filament.infolists.entries.ride-detail-extras')
                        ->columnSpanFull(),
                ]),
        ]);
    }

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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['passenger', 'driver', 'vehicle', 'vehicleCategory']))
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable()->sticky(),
                Tables\Columns\IconColumn::make('is_scheduled')
                    ->label('Planifiée')
                    ->boolean()
                    ->trueIcon('heroicon-o-calendar-days')
                    ->falseIcon('heroicon-o-bolt')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('scheduled_time')
                    ->label('Heure commandée')
                    ->formatStateUsing(fn ($state, Ride $record): string => $record->is_scheduled
                        ? UserTimezoneHelper::formatDateTime($state)
                        : '—')
                    ->color('info')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ride_status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => StatusColorHelper::rideStatusLabel($state))
                    ->color(fn (string $state): string => StatusColorHelper::rideStatusColor($state))
                    ->sticky(),
                Tables\Columns\ViewColumn::make('passenger')
                    ->label('Client')
                    ->view('filament.tables.columns.user-with-avatar-and-name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('passenger', function (Builder $userQuery) use ($search): Builder {
                            return $userQuery
                                ->where('firstname', 'like', "%{$search}%")
                                ->orWhere('lastname', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\ViewColumn::make('driver')
                    ->label('Chauffeur')
                    ->view('filament.tables.columns.user-with-avatar-and-name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('driver', function (Builder $userQuery) use ($search): Builder {
                            return $userQuery
                                ->where('firstname', 'like', "%{$search}%")
                                ->orWhere('lastname', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('distance')->label('Distance')->suffix(' km')->sortable(),
                CurrencyFormatter::configureMoneyColumn(
                    Tables\Columns\TextColumn::make('cost')->label('Coût')->sortable()
                ),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Paiement')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => StatusColorHelper::paymentMethodLabel($state))
                    ->color(fn (string $state): string => StatusColorHelper::paymentMethodColor($state)),
                Tables\Columns\IconColumn::make('paid')->label('Payé')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('ride_status')
                    ->label('Statut')
                    ->options([
                        'requested' => 'Demandée',
                        'accepted' => 'Acceptée',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminée',
                        'canceled' => 'Annulée',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Paiement')
                    ->options([
                        'cash' => 'Espèces',
                        'kintaxi-wallet' => 'Portefeuille Kintaxi',
                        'mobile-money' => 'Mobile Money',
                        'card' => 'Carte',
                    ])
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_scheduled')
                    ->label('Planifiée')
                    ->placeholder('Toutes')
                    ->trueLabel('Commandées')
                    ->falseLabel('Directes'),
                Tables\Filters\SelectFilter::make('passenger_id')
                    ->label('Client')
                    ->relationship('passenger', 'email')
                    ->getOptionLabelFromRecordUsing(fn (User $record): string => $record->getFilamentName())
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('driver_id')
                    ->label('Chauffeur')
                    ->relationship('driver', 'email')
                    ->getOptionLabelFromRecordUsing(fn (User $record): string => $record->getFilamentName())
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('assignDriver')
                    ->label('Affecter chauffeur')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn (Ride $record): bool => in_array($record->ride_status, ['requested', 'accepted'], true))
                    ->form([
                        Forms\Components\Select::make('driver_id')
                            ->label('Chauffeur (confort+, disponible)')
                            ->options(function (Ride $record): array {
                                return EligibleDriverQueryHelper::applyEligibleDrivers(User::query(), $record)
                                    ->orderBy('firstname')
                                    ->get()
                                    ->mapWithKeys(fn (User $driver): array => [
                                        $driver->id => $driver->getFilamentName().' — '.($driver->phone ?: 'sans tél.'),
                                    ])
                                    ->all();
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Seuls les chauffeurs avec véhicule confort ou supérieur, sans course en cours, sont listés.'),
                    ])
                    ->action(function (Ride $record, array $data): void {
                        $driver = User::query()->findOrFail((int) $data['driver_id']);
                        $vehicle = EligibleDriverQueryHelper::resolveVehicleForDriver(
                            $driver,
                            $record->vehicle_category_id ? (int) $record->vehicle_category_id : null
                        );

                        if (! $vehicle) {
                            Notification::make()
                                ->title('Aucun véhicule éligible')
                                ->body('Ce chauffeur n’a pas de véhicule confort ou supérieur actif.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $previousDriverId = $record->driver_id;
                        $record->update([
                            'driver_id' => $driver->id,
                            'vehicle_id' => $vehicle->id,
                            'vehicle_category_id' => $vehicle->category_id,
                            'ride_status' => $record->ride_status === 'requested' ? 'accepted' : $record->ride_status,
                        ]);

                        $smsSent = false;
                        if ($record->is_scheduled && $previousDriverId !== $driver->id) {
                            $smsSent = app(ScheduledRideDriverNotificationService::class)
                                ->notifyDriverAssignment($record->fresh(), $driver);
                        }

                        Notification::make()
                            ->title('Chauffeur affecté')
                            ->body(
                                $smsSent
                                    ? 'Le chauffeur a été affecté et informé par SMS.'
                                    : ($record->is_scheduled
                                        ? 'Le chauffeur a été affecté (SMS non envoyé : vérifiez le téléphone ou la config Keccel).'
                                        : 'Le chauffeur a été affecté à la course.')
                            )
                            ->success()
                            ->send();
                    }),
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
            'view' => Pages\ViewRide::route('/{record}'),
            'edit' => Pages\EditRide::route('/{record}/edit'),
        ];
    }
}
