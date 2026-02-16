<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $modelLabel = 'Document';

    protected static ?string $pluralModelLabel = 'Documents';

    protected static ?string $navigationGroup = 'Documents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Document')->schema([
                Forms\Components\Select::make('status_id')
                    ->label('Statut')
                    ->relationship('status', 'status_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => \App\Models\Status::formatShort($record->status_name ?? null))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('user_id')
                    ->label('Propriétaire')
                    ->relationship('user', 'email')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->phone ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'id_card' => 'Carte d\'identité',
                        'driving_license' => 'Permis de conduire',
                        'vehicle_registration' => 'Carte grise',
                        'vehicle_insurance' => 'Assurance véhicule',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('file_url')
                    ->label('URL du fichier')
                    ->url()
                    ->required()
                    ->maxLength(1000),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Véhicule')
                    ->relationship('vehicle', 'registration_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->registration_number ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('verified')->label('Vérifié')->default(0),
                Forms\Components\DateTimePicker::make('verified_at')->label('Date vérification'),
                Forms\Components\Select::make('verified_by')
                    ->label('Vérifié par')
                    ->relationship('verifier', 'email')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Type')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'id_card' => 'CNI',
                    'driving_license' => 'Permis',
                    'vehicle_registration' => 'Carte grise',
                    'vehicle_insurance' => 'Assurance',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('user.email')->label('Propriétaire')->searchable(),
                Tables\Columns\IconColumn::make('verified')->label('Vérifié')->boolean(),
                Tables\Columns\TextColumn::make('verified_at')->label('Vérifié le')->dateTime('d/m/Y'),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'id_card' => 'Carte d\'identité',
                    'driving_license' => 'Permis',
                    'vehicle_registration' => 'Carte grise',
                    'vehicle_insurance' => 'Assurance',
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view' => Pages\ViewDocument::route('/{record}'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
