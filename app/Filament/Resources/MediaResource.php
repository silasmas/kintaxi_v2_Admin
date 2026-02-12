<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Médias';

    protected static ?string $modelLabel = 'Média';

    protected static ?string $pluralModelLabel = 'Médias';

    protected static ?string $navigationGroup = 'Contenu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'image' => 'Image',
                        'video' => 'Vidéo',
                    ])
                    ->required()
                    ->live(),
                Forms\Components\FileUpload::make('path')
                    ->label('Fichier')
                    ->disk(env('FILAMENT_FILESYSTEM_DISK', 's3_media'))
                    ->directory(fn (Forms\Get $get): string => $get('type') === 'video' ? 'videos' : 'images')
                    ->visibility('public')
                    ->image(fn (Forms\Get $get): bool => $get('type') === 'image')
                    ->imagePreviewHeight('250')
                    ->maxSize(512000) // 512 Mo pour les vidéos
                    ->acceptedFileTypes(fn (Forms\Get $get): array => $get('type') === 'video'
                        ? ['video/mp4', 'video/webm', 'video/quicktime']
                        : ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->required()
                    ->afterStateUpdated(function (Forms\Set $set, $state): void {
                        if (is_string($state)) {
                            $set('name', $set('name') ?: Str::beforeLast(basename($state), '.'));
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('Aperçu')
                    ->disk(fn (Media $record) => $record->disk ?? 's3_media')
                    ->visibility('public')
                    ->defaultImageUrl(fn (Media $record) => $record->type === 'video'
                        ? 'https://placehold.co/80x80?text=Video'
                        : null)
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'image' => 'success',
                        'video' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('size')
                    ->label('Taille')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 1) . ' Ko' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options(['image' => 'Image', 'video' => 'Vidéo']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
