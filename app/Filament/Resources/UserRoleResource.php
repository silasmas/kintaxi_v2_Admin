<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserRoleResource\Pages;
use App\Models\UserRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserRoleResource extends Resource
{
    protected static ?string $model = UserRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Rôles';

    protected static ?string $modelLabel = 'Rôle';

    protected static ?string $pluralModelLabel = 'Rôles';

    protected static ?string $navigationGroup = 'Référentiels';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('role_name')->label('Nom'),
            TextEntry::make('role_description')->label('Description')->columnSpanFull(),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Rôle')->schema([
                Forms\Components\TextInput::make('role_name')
                    ->label('Nom du rôle')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('role_description')
                    ->label('Description')
                    ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('role_name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('role_description')->label('Description')->limit(50),
                Tables\Columns\TextColumn::make('users_count')->label('Utilisateurs')->counts('users'),
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
            'index' => Pages\ListUserRoles::route('/'),
            'create' => Pages\CreateUserRole::route('/create'),
            'view' => Pages\ViewUserRole::route('/{record}'),
            'edit' => Pages\EditUserRole::route('/{record}/edit'),
        ];
    }
}
