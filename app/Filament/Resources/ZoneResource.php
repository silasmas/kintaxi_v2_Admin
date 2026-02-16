<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZoneResource\Pages;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Zones';

    protected static ?string $modelLabel = 'Zone';

    protected static ?string $pluralModelLabel = 'Zones';

    protected static ?string $navigationGroup = 'Référentiels';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name')->label('Nom'),
                TextEntry::make('code')->label('Code'),
                TextEntry::make('description')->label('Description')->columnSpanFull(),
                TextEntry::make('country.name_fr')
                    ->label('Pays')
                    ->formatStateUsing(fn (?string $state): string => \App\Filament\Resources\ZoneResource::formatCountryAsRdc($state)),
                TextEntry::make('latitude')->label('Latitude'),
                TextEntry::make('longitude')->label('Longitude'),
                TextEntry::make('radius_km')->label('Rayon (km)'),
                TextEntry::make('is_active')
                    ->label('Active')
                    ->formatStateUsing(fn (?bool $state): string => $state ? 'Oui' : 'Non')
                    ->badge()
                    ->color(fn (?bool $state): string => $state ? 'success' : 'danger'),
            ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Zone')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('code')
                        ->label('Code')
                        ->maxLength(20),
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('country_id')
                        ->label('Pays')
                        ->relationship('country', 'name_fr')
                        ->getOptionLabelFromRecordUsing(fn ($record) => self::formatCountryAsRdc($record->name_fr ?? null))
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('latitude')
                        ->label('Latitude')
                        ->numeric()
                        ->step(0.00000001),
                    Forms\Components\TextInput::make('longitude')
                        ->label('Longitude')
                        ->numeric()
                        ->step(0.00000001),
                    Forms\Components\TextInput::make('radius_km')
                        ->label('Rayon (km)')
                        ->numeric()
                        ->step(0.01),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country.name_fr')
                    ->label('Pays')
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => self::formatCountryAsRdc($state)),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
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

    public static function formatCountryAsRdc(?string $name): string
    {
        if ($name === null || $name === '') {
            return '—';
        }
        $rdcVariants = ['République Démocratique du Congo', 'République democratique du Congo', 'Democratic Republic of the Congo'];
        foreach ($rdcVariants as $variant) {
            if (stripos($name, $variant) !== false) {
                return 'RDC';
            }
        }

        return $name;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'view' => Pages\ViewZone::route('/{record}'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
