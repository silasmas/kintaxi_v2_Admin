<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Statuts';

    protected static ?string $modelLabel = 'Statut';

    protected static ?string $pluralModelLabel = 'Statuts';

    protected static ?string $navigationGroup = 'Référentiels';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('status_name')->label('Nom'),
            TextEntry::make('status_description')->label('Description')->columnSpanFull(),
            TextEntry::make('color')->label('Couleur'),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Statut')->schema([
                Forms\Components\TextInput::make('status_name')
                    ->label('Nom du statut')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('status_description')
                    ->label('Description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('icon')
                    ->label('Icône')
                    ->maxLength(45),
                Forms\Components\ColorPicker::make('color')
                    ->label('Couleur'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status_name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status_description')->label('Description')->limit(40),
                Tables\Columns\TextColumn::make('color')->label('Couleur')->badge(),
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
            'index' => Pages\ListStatuses::route('/'),
            'create' => Pages\CreateStatus::route('/create'),
            'view' => Pages\ViewStatus::route('/{record}'),
            'edit' => Pages\EditStatus::route('/{record}/edit'),
        ];
    }
}
