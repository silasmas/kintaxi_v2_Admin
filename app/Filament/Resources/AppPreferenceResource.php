<?php

namespace App\Filament\Resources;

use App\Enums\AppPreferenceType;
use App\Filament\Resources\AppPreferenceResource\Pages;
use App\Models\AppPreference;
use App\Support\PreferenceDisplayFormatter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppPreferenceResource extends Resource
{
    protected static ?string $model = AppPreference::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $modelLabel = 'Préférence';

    protected static ?string $pluralModelLabel = 'Réglages (avancé)';

    protected static ?string $navigationGroup = 'Paramètres';

    protected static bool $shouldRegisterNavigation = false;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('pref_name')->label('Titre'),
                Infolists\Components\TextEntry::make('pref_description')
                    ->label('Description')
                    ->placeholder('—')
                    ->columnSpanFull()
                    ->formatStateUsing(function (?string $state): string {
                        $formatted = PreferenceDisplayFormatter::formatHumanText($state);

                        return ($formatted !== null && $formatted !== '') ? $formatted : '—';
                    }),
                Infolists\Components\TextEntry::make('pref_type')
                    ->label('Type')
                    ->formatStateUsing(fn (AppPreferenceType $state): string => $state->label()),
                Infolists\Components\TextEntry::make('pref_expected_value')
                    ->label('Valeurs attendues / options')
                    ->placeholder('—')
                    ->columnSpanFull(),
                Infolists\Components\TextEntry::make('pref_value')
                    ->label('Valeur actuelle')
                    ->columnSpanFull()
                    ->formatStateUsing(fn (?string $state): string => PreferenceDisplayFormatter::formatHumanText($state ?? '') ?? '—'),
                Infolists\Components\TextEntry::make('pref_key')
                    ->label('Clé technique (pour le code uniquement)')
                    ->copyable(),
                Infolists\Components\TextEntry::make('updatedBy.email')
                    ->label('Dernière modification par')
                    ->placeholder('—'),
                Infolists\Components\TextEntry::make('updated_at')->label('Mis à jour le')->dateTime('d/m/Y H:i'),
            ])->columns(2),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Affichage (tableau des réglages)')
                ->schema([
                    Forms\Components\TextInput::make('pref_name')
                        ->label('Titre')
                        ->required()
                        ->maxLength(200),
                    Forms\Components\Textarea::make('pref_description')
                        ->label('Description')
                        ->rows(4)
                        ->maxLength(2000)
                        ->helperText('Explications pour les admins : ce texte s’affiche sous le titre (sans la clé technique). Le symbole $ sera affiché comme « Dollar ».')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('pref_type')
                        ->label('Type de valeur')
                        ->options(collect(AppPreferenceType::cases())->mapWithKeys(
                            fn (AppPreferenceType $t) => [$t->value => $t->label()]
                        ))
                        ->required()
                        ->live(),
                    Forms\Components\Textarea::make('pref_expected_value')
                        ->label('Options ou contrainte')
                        ->rows(2)
                        ->helperText(function (Get $get): string {
                            return match ($get('pref_type')) {
                                AppPreferenceType::Radio->value, AppPreferenceType::MultipleChoice->value => 'Liste d’options séparées par des virgules (ex. option_a, option_b, option_c).',
                                AppPreferenceType::Number->value => 'Optionnel : min,max (ex. 0,1000) ou valeur par défaut.',
                                default => 'Optionnel : valeur par défaut ou texte d’aide.',
                            };
                        })
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Réglage technique')
                ->description('Réservé au développement : identifiant unique pour l’application et les API.')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('pref_key')
                        ->label('Clé technique')
                        ->required()
                        ->maxLength(100)
                        ->regex('/^[a-z0-9_]+$/')
                        ->helperText('Minuscules, chiffres et underscore uniquement.')
                        ->disabledOn('edit'),
                ])
                ->columns(2),
            Forms\Components\Section::make('Valeur par défaut / courante')
                ->schema([
                    Forms\Components\Textarea::make('pref_value')
                        ->label('Valeur courante')
                        ->rows(4)
                        ->maxLength(1000)
                        ->helperText(fn (Get $get): string => match ($get('pref_type')) {
                            AppPreferenceType::MultipleChoice->value => 'Plusieurs valeurs séparées par des virgules.',
                            AppPreferenceType::Radio->value => 'Une seule valeur parmi les options définies ci-dessus.',
                            AppPreferenceType::Number->value => 'Nombre.',
                            default => 'Texte libre.',
                        })
                        ->rules(fn (Get $get): array => $get('pref_type') === AppPreferenceType::Number->value
                            ? ['nullable', 'numeric']
                            : ['nullable', 'string', 'max:1000'])
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pref_name')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('pref_description')
                    ->label('Description')
                    ->limit(80)
                    ->tooltip(fn (?string $state): ?string => $state ? PreferenceDisplayFormatter::formatHumanText($state) : null)
                    ->formatStateUsing(fn (?string $state): string => $state ? (PreferenceDisplayFormatter::formatHumanText($state) ?? '') : '—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pref_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (AppPreferenceType $state): string => $state->label()),
                Tables\Columns\TextColumn::make('pref_value')
                    ->label('Valeur')
                    ->limit(40)
                    ->formatStateUsing(fn (?string $state): string => PreferenceDisplayFormatter::formatHumanText($state ?? '') ?? '—')
                    ->tooltip(fn (?string $state): ?string => $state ? PreferenceDisplayFormatter::formatHumanText($state) : null)
                    ->wrap(),
                Tables\Columns\TextColumn::make('updatedBy.email')
                    ->label('Modifié par')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('pref_name')
            ->filters([
                Tables\Filters\SelectFilter::make('pref_type')
                    ->label('Type')
                    ->options(collect(AppPreferenceType::cases())->mapWithKeys(
                        fn (AppPreferenceType $t) => [$t->value => $t->label()]
                    )),
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
            'index' => Pages\ListAppPreferences::route('/'),
            'create' => Pages\CreateAppPreference::route('/create'),
            'view' => Pages\ViewAppPreference::route('/{record}'),
            'edit' => Pages\EditAppPreference::route('/{record}/edit'),
        ];
    }
}
