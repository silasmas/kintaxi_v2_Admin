<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
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
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?string $navigationGroup = 'Gestion';

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
                        TextEntry::make('status.status_name')->label('Statut')->badge(),
                        TextEntry::make('role.role_name')->label('Rôle app'),
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
                        TextEntry::make('wallet_balance')
                            ->label('Solde portefeuille')
                            ->money('XAF')
                            ->color(fn ($state) => ($state ?? 0) >= 0 ? 'success' : 'danger'),
                        TextEntry::make('loyalty_point')->label('Points fidélité')->numeric(),
                    ])
                    ->columns(2),
                Section::make('Véhicule(s)')
                    ->description('Véhicules possédés par l\'utilisateur')
                    ->schema([
                        TextEntry::make('vehicles_count')
                            ->label('Nombre de véhicules')
                            ->state(fn (User $record): int => $record->vehicles()->count())
                            ->default(0),
                        RepeatableEntry::make('vehicles')
                            ->label('')
                            ->schema([
                                TextEntry::make('registration_number')->label('Immatriculation'),
                                TextEntry::make('mark')->label('Marque'),
                                TextEntry::make('model')->label('Modèle'),
                                TextEntry::make('color')->label('Couleur'),
                                TextEntry::make('shape.name')->label('Type'),
                            ])
                            ->columns(2)
                            ->contained()
                            ->hiddenLabel(),
                    ])
                    ->visible(fn (User $record): bool => $record->vehicles()->count() > 0),
                Section::make('Aucun véhicule')
                    ->description('Cet utilisateur ne possède aucun véhicule enregistré.')
                    ->schema([
                        TextEntry::make('no_vehicles')
                            ->label('')
                            ->state('Aucun véhicule enregistré')
                            ->hiddenLabel(),
                    ])
                    ->visible(fn (User $record): bool => $record->vehicles()->count() === 0),
                Section::make('Véhicule actuel')
                    ->schema([
                        TextEntry::make('currentVehicle.registration_number')->label('Immatriculation'),
                        TextEntry::make('currentVehicle.mark')->label('Marque'),
                        TextEntry::make('currentVehicle.model')->label('Modèle'),
                    ])
                    ->columns(2)
                    ->visible(fn (User $record): bool => $record->currentVehicle !== null),
                Section::make('Transactions')
                    ->schema([
                        RepeatableEntry::make('transactions')
                            ->label('')
                            ->schema([
                                TextEntry::make('type')->label('Type'),
                                TextEntry::make('amount')->label('Montant')->money('XAF'),
                                TextEntry::make('wallet_balance_after')->label('Solde après')->money('XAF'),
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
                Section::make('Documents')
                    ->schema([
                        RepeatableEntry::make('documents')
                            ->label('')
                            ->schema([
                                TextEntry::make('type')->label('Type'),
                                TextEntry::make('verified')
                                    ->label('Vérifié')
                                    ->formatStateUsing(fn ($state) => $state ? 'Oui' : 'Non')
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                            ])
                            ->columns(2)
                            ->contained()
                            ->hiddenLabel(),
                    ])
                    ->visible(fn (User $record): bool => $record->documents()->count() > 0),
                Section::make('Aucun document')
                    ->description('Aucun document enregistré.')
                    ->schema([
                        TextEntry::make('no_documents')
                            ->label('')
                            ->state('Aucun document')
                            ->hiddenLabel(),
                    ])
                    ->visible(fn (User $record): bool => $record->documents()->count() === 0),
                Section::make('Trajets (conducteur)')
                    ->schema([
                        TextEntry::make('rides_driver_count')
                            ->label('Nombre de trajets')
                            ->state(fn (User $record): int => $record->ridesAsDriver()->count()),

                    ])
                    ->visible(fn (User $record): bool => $record->ridesAsDriver()->count() > 0),
                Section::make('Trajets (passager)')
                    ->schema([
                        TextEntry::make('rides_passenger_count')
                            ->label('Nombre de trajets')
                            ->state(fn (User $record): int => $record->ridesAsPassenger()->count()),
                    ])
                    ->visible(fn (User $record): bool => $record->ridesAsPassenger()->count() > 0),
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
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('firstname')->label('Prénom')->searchable(),
                Tables\Columns\TextColumn::make('lastname')->label('Nom')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('role.role_name')->label('Rôle app')->badge(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles Shield')
                    ->badge(),
                Tables\Columns\TextColumn::make('wallet_balance')->label('Solde')->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')->relationship('role', 'role_name')->label('Rôle'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
