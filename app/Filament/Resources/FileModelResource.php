<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileModelResource\Pages;
use App\Models\FileModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FileModelResource extends Resource
{
    protected static ?string $model = FileModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Fichiers';

    protected static ?string $modelLabel = 'Fichier';

    protected static ?string $pluralModelLabel = 'Fichiers';

    protected static ?string $navigationGroup = 'Documents';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('file_name')->label('Nom'),
            TextEntry::make('vehicle.registration_number')->label('Véhicule'),
            TextEntry::make('file_url')->label('URL')->url(),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Fichier')->schema([
                Forms\Components\Select::make('status_id')
                    ->label('Statut')
                    ->relationship('status', 'status_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => \App\Models\Status::formatShort($record->status_name ?? null))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Véhicule')
                    ->relationship('vehicle', 'registration_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->registration_number ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('file_name')->label('Nom du fichier')->maxLength(255),
                Forms\Components\TextInput::make('file_url')->label('URL')->url()->required(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('file_name')->label('Nom')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('vehicle.registration_number')->label('Véhicule'),
                Tables\Columns\TextColumn::make('status.status_name')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => \App\Models\Status::formatShort($state)),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y')->sortable(),
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
            'index' => Pages\ListFileModels::route('/'),
            'create' => Pages\CreateFileModel::route('/create'),
            'view' => Pages\ViewFileModel::route('/{record}'),
            'edit' => Pages\EditFileModel::route('/{record}/edit'),
        ];
    }
}
