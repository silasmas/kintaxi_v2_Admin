<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\DocumentsRelationManager;
use App\Filament\RelationManagers\MediasRelationManager;
use App\Filament\RelationManagers\RidesAsDriverRelationManager;
use App\Filament\RelationManagers\RidesAsPassengerRelationManager;
use App\Filament\RelationManagers\VehicleFilesRelationManager;
use App\Filament\RelationManagers\VehiclesRelationManager;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\KycVerificationsRelationManager;
use App\Filament\Support\CurrencyFormatter;
use App\Filament\Support\StatusColorHelper;
use App\Filament\Support\VehicleStatusHelper;
use App\Filament\Resources\UserResource\RelationManagers\LoyaltyHistoriesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\LoyaltyRedemptionHistoriesRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?string $navigationGroup = 'Gestion';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['latestKycVerification', 'role']);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Profil')
                    ->schema([
                        ViewEntry::make('avatar')
                            ->view('filament.infolists.entries.user-avatar')
                            ->columnSpanFull()
                            ->hiddenLabel(),
                        TextEntry::make('firstname')->label('Prénom'),
                        TextEntry::make('lastname')->label('Nom'),
                        TextEntry::make('surname')->label('Surnom'),
                        TextEntry::make('username')->label('Nom d\'utilisateur'),
                        TextEntry::make('email')->label('Email'),
                        TextEntry::make('phone')->label('Téléphone'),
                        TextEntry::make('gender')
                            ->label('Genre')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'M' => 'Homme',
                                'F' => 'Femme',
                                'other' => 'Autre',
                                default => '—',
                            }),
                        TextEntry::make('birthdate')->label('Date de naissance')->date('d/m/Y'),
                        TextEntry::make('status.status_name')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => \App\Models\Status::formatShort($state))
                            ->color(fn (?string $state): string => StatusColorHelper::statusNameColor($state)),
                        TextEntry::make('role.role_name')
                            ->label('Rôle app')
                            ->badge()
                            ->color(fn (?string $state): string => StatusColorHelper::roleColor($state)),
                        TextEntry::make('roles.name')->label('Rôles Shield')->badge(),
                    ])
                    ->columns(2),
                Section::make('Contact & Adresse')
                    ->schema([
                        TextEntry::make('country_code')->label('Code pays'),
                        TextEntry::make('city')->label('Ville'),
                        TextEntry::make('address_1')->label('Adresse 1')->columnSpanFull(),
                        TextEntry::make('address_2')->label('Adresse 2')->columnSpanFull(),
                        TextEntry::make('p_o_box')->label('Boîte postale'),
                    ])
                    ->columns(2)
                    ->collapsed(),
                Section::make('Portefeuille & Fidélité')
                    ->schema([
                        CurrencyFormatter::configureMoneyEntry(
                            TextEntry::make('wallet_balance')->label('Solde portefeuille')
                        )->color(fn ($state) => ($state ?? 0) >= 0 ? 'success' : 'danger'),
                        TextEntry::make('loyalty_point')->label('Points fidélité')->numeric(),
                    ])
                    ->columns(2),
                Section::make('KYC (Smile ID)')
                    ->schema([
                        TextEntry::make('kyc_verified')
                            ->label('Compte marqué vérifié')
                            ->formatStateUsing(fn ($state): string => $state ? 'Oui' : 'Non')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'warning'),
                        TextEntry::make('kyc_verified_at')->label('Date vérification compte')->dateTime('d/m/Y H:i'),
                        TextEntry::make('latestKycVerification.status')
                            ->label('Dernier statut job')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'under_review' => 'warning',
                                'pending' => 'gray',
                                default => 'info',
                            })
                            ->default('—'),
                        TextEntry::make('latestKycVerification.job_id')->label('Dernier job ID')->default('—'),
                        TextEntry::make('latestKycVerification.document_type')->label('Type document')->default('—'),
                        TextEntry::make('latestKycVerification.verified_at')->label('Dernier résultat reçu le')->dateTime('d/m/Y H:i')->default('—'),
                    ])
                    ->columns(2)
                    ->collapsed(),
                Section::make('Véhicules soumis')
                    ->description('Voir l\'onglet « Véhicules soumis » pour la liste complète et les pièces jointes.')
                    ->schema([
                        TextEntry::make('vehicles_count')
                            ->label('Nombre de véhicules')
                            ->state(fn (User $record): int => $record->vehicles()->count())
                            ->default(0),
                        TextEntry::make('vehicles_validated_count')
                            ->label('Validés')
                            ->state(fn (User $record): int => VehicleStatusHelper::applyValidated($record->vehicles())->count())
                            ->default(0),
                        TextEntry::make('vehicles_failed_count')
                            ->label('Échoués / refusés')
                            ->state(fn (User $record): int => VehicleStatusHelper::applyFailed($record->vehicles())->count())
                            ->default(0),
                    ])
                    ->columns(3),
                Section::make('Véhicule actuel')
                    ->schema([
                        TextEntry::make('currentVehicle.registration_number')->label('Immatriculation'),
                        TextEntry::make('currentVehicle.mark')->label('Marque'),
                        TextEntry::make('currentVehicle.model')->label('Modèle'),
                    ])
                    ->columns(2)
                    ->visible(fn (User $record): bool => $record->currentVehicle !== null),
                Section::make('Transactions')
                    ->collapsed()
                    ->schema([
                        RepeatableEntry::make('transactions')
                            ->label('')
                            ->schema([
                                TextEntry::make('type')->label('Type'),
                                CurrencyFormatter::configureMoneyEntry(
                                    TextEntry::make('amount')->label('Montant')
                                ),
                                CurrencyFormatter::configureMoneyEntry(
                                    TextEntry::make('wallet_balance_after')->label('Solde après')
                                ),
                                TextEntry::make('created_at')->label('Date')->dateTime('d/m/Y H:i'),
                            ])
                            ->columns(4)
                            ->contained()
                            ->hiddenLabel(),
                    ])
                    ->visible(fn (User $record): bool => $record->transactions()->count() > 0),
                Section::make('Aucune transaction')
                    ->description('Aucune transaction enregistrée.')
                    ->schema([
                        TextEntry::make('no_transactions')
                            ->label('')
                            ->state('Aucune transaction')
                            ->hiddenLabel(),
                    ])
                    ->visible(fn (User $record): bool => $record->transactions()->count() === 0),
                Section::make('Activité courses')
                    ->schema([
                        TextEntry::make('rides_driver_count')
                            ->label('Courses chauffeur')
                            ->state(fn (User $record): int => $record->ridesAsDriver()->count())
                            ->default(0),
                        TextEntry::make('rides_passenger_count')
                            ->label('Courses client')
                            ->state(fn (User $record): int => $record->ridesAsPassenger()->count())
                            ->default(0),
                    ])
                    ->columns(2)
                    ->description('Détail dans les onglets « Courses (chauffeur) » et « Courses (client) ».'),
                Section::make('Avis reçus')
                    ->schema([
                        TextEntry::make('reviews_received_count')
                            ->label('Nombre d\'avis')
                            ->state(fn (User $record): int => $record->reviewsReceived()->count()),
                        TextEntry::make('rate')->label('Note moyenne')->numeric(decimalPlaces: 1),
                    ])
                    ->columns(2)
                    ->visible(fn (User $record): bool => $record->reviewsReceived()->count() > 0),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identité')->schema([
                    Forms\Components\Select::make('status_id')
                        ->label('Statut')
                        ->relationship('status', 'status_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => \App\Models\Status::formatShort($record->status_name ?? null))
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('role_id')
                        ->label('Rôle')
                        ->relationship('role', 'role_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->role_name ?? $record->id ?? '—'))
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('firstname')->label('Prénom')->maxLength(255),
                    Forms\Components\TextInput::make('lastname')->label('Nom')->maxLength(255),
                    Forms\Components\TextInput::make('surname')->label('Surnom')->maxLength(255),
                    Forms\Components\TextInput::make('username')->label('Nom d\'utilisateur')->maxLength(255),
                    Forms\Components\TextInput::make('email')->email()->maxLength(255),
                    Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                    Forms\Components\Select::make('gender')
                        ->label('Genre')
                        ->options(['M' => 'Homme', 'F' => 'Femme', 'other' => 'Autre']),
                    Forms\Components\DatePicker::make('birthdate')->label('Date de naissance'),
                ])->columns(2),
                Forms\Components\Section::make('Contact & Adresse')->schema([
                    Forms\Components\TextInput::make('country_code')->label('Code pays')->maxLength(4),
                    Forms\Components\TextInput::make('city')->label('Ville')->maxLength(45),
                    Forms\Components\Textarea::make('address_1')->label('Adresse 1')->columnSpanFull(),
                    Forms\Components\Textarea::make('address_2')->label('Adresse 2')->columnSpanFull(),
                    Forms\Components\TextInput::make('p_o_box')->label('Boîte postale')->maxLength(45),
                ])->columns(2)->collapsed(),
                Forms\Components\Section::make('Rôles & Permissions (Shield)')->schema([
                    Forms\Components\CheckboxList::make('roles')
                        ->relationship('roles', 'name')
                        ->label('Rôles Filament')
                        ->searchable()
                        ->columns(2),
                ])->columns(1)->collapsed(),
                Forms\Components\Section::make('Sécurité & Compte')->schema([
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->maxLength(255),
                    Forms\Components\TextInput::make('wallet_balance')->label('Solde portefeuille')->numeric()->default(0),
                    Forms\Components\TextInput::make('loyalty_point')->label('Points fidélité')->numeric()->default(0),
                    Forms\Components\Select::make('current_vehicle_id')
                        ->label('Véhicule actuel')
                        ->relationship('currentVehicle', 'registration_number')
                        ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->registration_number ?? $record->id ?? '—'))
                        ->searchable()
                        ->preload(),
                ])->columns(2)->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable()->sticky(),
                Tables\Columns\ViewColumn::make('avatar')
                    ->label('')
                    ->state(fn (User $record): User => $record)
                    ->view('filament.tables.columns.owner-with-avatar')
                    ->sortable(false)
                    ->sticky(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Utilisateur')
                    ->state(fn (User $record): string => $record->getFilamentName())
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->sticky(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('role.role_name')
                    ->label('Rôle app')
                    ->badge()
                    ->color(fn (?string $state): string => StatusColorHelper::roleColor($state)),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles Shield')
                    ->badge(),
                CurrencyFormatter::configureMoneyColumn(
                    Tables\Columns\TextColumn::make('wallet_balance')->label('Solde')->sortable()
                ),
                Tables\Columns\IconColumn::make('kyc_verified')->label('KYC')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('latestKycVerification.status')
                    ->label('Statut KYC')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'under_review' => 'warning',
                        'pending' => 'gray',
                        default => 'info',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')->relationship('role', 'role_name')->label('Rôle'),
                Tables\Filters\TernaryFilter::make('has_vehicles')
                    ->label('Possède des véhicules')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('vehicles'),
                        false: fn (Builder $query) => $query->whereDoesntHave('vehicles'),
                    ),
                Tables\Filters\SelectFilter::make('vehicle_validation')
                    ->label('Véhicules (statut)')
                    ->options([
                        'validated' => 'Au moins un véhicule validé',
                        'failed' => 'Au moins un véhicule échoué',
                        'pending' => 'Au moins un véhicule en attente',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if ($value === 'validated') {
                            return $query->whereHas('vehicles', fn (Builder $vehicleQuery) => VehicleStatusHelper::applyValidated($vehicleQuery));
                        }
                        if ($value === 'failed') {
                            return $query->whereHas('vehicles', fn (Builder $vehicleQuery) => VehicleStatusHelper::applyFailed($vehicleQuery));
                        }
                        if ($value === 'pending') {
                            return $query->whereHas('vehicles', fn (Builder $vehicleQuery) => VehicleStatusHelper::applyPending($vehicleQuery));
                        }

                        return $query;
                    }),
                Tables\Filters\TernaryFilter::make('has_rides_as_driver')
                    ->label('A effectué des courses (chauffeur)')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('ridesAsDriver'),
                        false: fn (Builder $query) => $query->whereDoesntHave('ridesAsDriver'),
                    ),
                Tables\Filters\TernaryFilter::make('has_rides_as_passenger')
                    ->label('A passé des courses (client)')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('ridesAsPassenger'),
                        false: fn (Builder $query) => $query->whereDoesntHave('ridesAsPassenger'),
                    ),
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
        return [
            VehiclesRelationManager::class,
            RidesAsDriverRelationManager::class,
            RidesAsPassengerRelationManager::class,
            DocumentsRelationManager::class,
            VehicleFilesRelationManager::class,
            MediasRelationManager::class,
            LoyaltyHistoriesRelationManager::class,
            LoyaltyRedemptionHistoriesRelationManager::class,
            KycVerificationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
