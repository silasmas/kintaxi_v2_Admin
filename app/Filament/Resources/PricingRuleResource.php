<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingRuleResource\Pages;
use App\Models\PricingRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PricingRuleResource extends Resource
{
    protected static ?string $model = PricingRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Règles tarifaires';

    protected static ?string $modelLabel = 'Règle tarifaire';

    protected static ?string $pluralModelLabel = 'Règles tarifaires';

    protected static ?string $navigationGroup = 'Tarification';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    private static function ruleTypeLabel(?string $value): string
    {
        return match ($value) {
            'base_fare' => 'Tarif de base',
            'distance' => 'Distance parcourue',
            'time' => 'Durée de trajet',
            'waiting_time' => 'Temps d\'attente',
            'traffic' => 'Surcharge trafic',
            default => $value ?: '—',
        };
    }

    private static function unitLabel(?string $value): string
    {
        return match ($value) {
            'km' => 'Kilomètre (km)',
            'min' => 'Minute (min)',
            'fixed' => 'Montant fixe',
            'percentage' => 'Pourcentage (%)',
            default => $value ?: '—',
        };
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('rule_type')->label('Type')->formatStateUsing(fn (?string $state): string => self::ruleTypeLabel($state)),
            TextEntry::make('unit')->label('Unité')->formatStateUsing(fn (?string $state): string => self::unitLabel($state)),
            TextEntry::make('cost')->label('Coût'),
            TextEntry::make('vehicleCategory.category_name')->label('Catégorie'),
            TextEntry::make('zone.name')->label('Zone')->default('—'),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Règle tarifaire')->schema([
                    Forms\Components\Select::make('rule_type')
                        ->label('Type de règle')
                        ->options([
                            'base_fare' => 'Prix de base',
                            'distance' => 'Distance',
                            'time' => 'Temps',
                            'waiting_time' => 'Temps d\'attente',
                            'traffic' => 'Trafic',
                        ])
                        ->required(),
                    Forms\Components\Select::make('unit')
                        ->label('Unité')
                        ->options([
                            'km' => 'Kilomètre (km)',
                            'min' => 'Minute (min)',
                            'fixed' => 'Montant fixe',
                            'percentage' => 'Pourcentage (%)',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('min_value')->label('Valeur min')->numeric()->step(0.01),
                    Forms\Components\TextInput::make('max_value')->label('Valeur max')->numeric()->step(0.01),
                    Forms\Components\TextInput::make('cost')->label('Coût')->numeric()->step(0.01),
                    Forms\Components\Select::make('vehicle_category')
                        ->label('Catégorie véhicule')
                        ->relationship('vehicleCategory', 'category_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->category_name ?? $record->id ?? '—'))
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('zone_id')
                        ->label('Zone tarifaire')
                        ->relationship('zone', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('surge_multiplier')->label('Multiplicateur surcharge')->numeric()->default(1)->step(0.01),
                    Forms\Components\Toggle::make('is_default')->label('Par défaut')->default(0),
                    Forms\Components\DateTimePicker::make('valid_from')->label('Valide du'),
                    Forms\Components\DateTimePicker::make('valid_to')->label('Valide jusqu\'au'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rule_type')->label('Type')->badge()
                    ->formatStateUsing(fn (?string $state): string => self::ruleTypeLabel($state)),
                Tables\Columns\TextColumn::make('unit')->label('Unité')->badge()
                    ->formatStateUsing(fn (?string $state): string => self::unitLabel($state)),
                Tables\Columns\TextColumn::make('cost')->label('Coût')->numeric(decimalPlaces: 2)->sortable(),
                Tables\Columns\TextColumn::make('vehicleCategory.category_name')->label('Catégorie'),
                Tables\Columns\TextColumn::make('zone.name')->label('Zone')->default('—')->toggleable(),
                Tables\Columns\IconColumn::make('is_default')->label('Défaut')->boolean(),
                Tables\Columns\TextColumn::make('valid_from')->label('Valide du')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rule_type')->options([
                    'base_fare' => 'Prix de base',
                    'distance' => 'Distance',
                    'time' => 'Temps',
                    'waiting_time' => 'Temps d\'attente',
                    'traffic' => 'Trafic',
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
            'index' => Pages\ListPricingRules::route('/'),
            'create' => Pages\CreatePricingRule::route('/create'),
            'view' => Pages\ViewPricingRule::route('/{record}'),
            'edit' => Pages\EditPricingRule::route('/{record}/edit'),
        ];
    }
}
