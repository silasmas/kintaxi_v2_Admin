<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PasswordResetResource\Pages;
use App\Models\PasswordReset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PasswordResetResource extends Resource
{
    protected static ?string $model = PasswordReset::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Réinitialisations mot de passe';

    protected static ?string $modelLabel = 'Réinitialisation';

    protected static ?string $pluralModelLabel = 'Réinitialisations mot de passe';

    protected static ?string $navigationGroup = 'Sécurité';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('email')->label('Email'),
            TextEntry::make('phone')->label('Téléphone'),
            TextEntry::make('created_at')->label('Date')->dateTime('d/m/Y H:i'),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Réinitialisation')->schema([
                Forms\Components\TextInput::make('email')->email()->maxLength(255),
                Forms\Components\TextInput::make('phone')->tel()->maxLength(45),
                Forms\Components\TextInput::make('token')->maxLength(45),
                Forms\Components\Textarea::make('former_password')->label('Ancien mot de passe')->required(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListPasswordResets::route('/'),
            'create' => Pages\CreatePasswordReset::route('/create'),
            'view' => Pages\ViewPasswordReset::route('/{record}'),
            'edit' => Pages\EditPasswordReset::route('/{record}/edit'),
        ];
    }
}
