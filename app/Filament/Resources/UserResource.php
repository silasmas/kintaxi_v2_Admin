<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
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
        return $infolist->schema([
            TextEntry::make('firstname')->label('Prénom'),
            TextEntry::make('lastname')->label('Nom'),
            TextEntry::make('email')->label('Email'),
            TextEntry::make('phone')->label('Téléphone'),
            TextEntry::make('role.role_name')->label('Rôle app'),
            TextEntry::make('roles.name')->label('Rôles Shield')->badge(),
            TextEntry::make('wallet_balance')->label('Solde'),
        ])->columns(2);
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
