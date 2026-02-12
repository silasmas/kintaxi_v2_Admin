<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppNotificationResource\Pages;
use App\Models\AppNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppNotificationResource extends Resource
{
    protected static ?string $model = AppNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $modelLabel = 'Notification';

    protected static ?string $pluralModelLabel = 'Notifications';

    protected static ?string $navigationGroup = 'Système';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('message')->label('Message'),
            TextEntry::make('toUser.email')->label('Destinataire'),
            TextEntry::make('viewed')->label('Lu')->formatStateUsing(fn ($s) => $s ? 'Oui' : 'Non'),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Notification')->schema([
                Forms\Components\Select::make('status_id')
                    ->label('Statut')
                    ->relationship('status', 'status_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->status_name ?? $record->id ?? '—'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('object_id')->label('ID objet')->required()->numeric(),
                Forms\Components\TextInput::make('object_name')->label('Table objet')->required()->maxLength(200),
                Forms\Components\Select::make('notification_from')
                    ->label('Expéditeur')
                    ->relationship('fromUser', 'email')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('notification_to')
                    ->label('Destinataire')
                    ->relationship('toUser', 'email')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->id ?? '—'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('viewed')->label('Lu')->default(0),
                Forms\Components\TextInput::make('message')->label('Message')->required()->maxLength(100),
                Forms\Components\Textarea::make('metadata')->label('Métadonnées (JSON)')->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('message')->label('Message')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('toUser.email')->label('Destinataire')->searchable(),
                Tables\Columns\IconColumn::make('viewed')->label('Lu')->boolean(),
                Tables\Columns\TextColumn::make('object_name')->label('Objet'),
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
            'index' => Pages\ListAppNotifications::route('/'),
            'create' => Pages\CreateAppNotification::route('/create'),
            'view' => Pages\ViewAppNotification::route('/{record}'),
            'edit' => Pages\EditAppNotification::route('/{record}/edit'),
        ];
    }
}
