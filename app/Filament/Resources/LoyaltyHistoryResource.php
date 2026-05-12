<?php

namespace App\Filament\Resources;

use App\Enums\LoyaltyTransactionType;
use App\Filament\Resources\LoyaltyHistoryResource\Pages;
use App\Models\LoyaltyHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoyaltyHistoryResource extends Resource
{
    protected static ?string $model = LoyaltyHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Page de fidélité';

    protected static ?string $modelLabel = 'Mouvement';

    protected static ?string $pluralModelLabel = 'Mouvements';

    protected static ?string $navigationGroup = 'Fidélité';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Mouvement')->schema([
                Infolists\Components\TextEntry::make('user_display')
                    ->label('Utilisateur')
                    ->state(fn (LoyaltyHistory $record): string => $record->user?->getFilamentName() ?? '—'),
                Infolists\Components\TextEntry::make('transaction_type')
                    ->label('Type')
                    ->formatStateUsing(fn (LoyaltyTransactionType $state): string => $state->label())
                    ->badge()
                    ->color(fn (LoyaltyTransactionType $state): string => $state->color()),
                Infolists\Components\TextEntry::make('points_earned')->label('Points (mouvement)'),
                Infolists\Components\TextEntry::make('points_before_transaction')->label('Solde avant'),
                Infolists\Components\TextEntry::make('points_after_transaction')->label('Solde après'),
                Infolists\Components\TextEntry::make('reference_id')->label('Référence métier'),
                Infolists\Components\TextEntry::make('description')->label('Description')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->label('Date')->dateTime('d/m/Y H:i'),
            ])->columns(2),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Données du mouvement')
                ->description('Règle métier : solde après = solde avant + points du mouvement. À la création, les points fidélité de l’utilisateur sont alignés sur « solde après ».')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Utilisateur')
                        ->relationship('user', 'firstname')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record instanceof \App\Models\User
                            ? ($record->getFilamentName() ?: (string) ($record->email ?? $record->phone ?? '#'.$record->id))
                            : (string) $record)
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('transaction_type')
                        ->label('Type de transaction')
                        ->options(collect(LoyaltyTransactionType::cases())->mapWithKeys(
                            fn (LoyaltyTransactionType $t) => [$t->value => $t->label()]
                        ))
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('points_earned')
                        ->label('Points (gain ou perte)')
                        ->required()
                        ->integer()
                        ->helperText('Positif = crédit, négatif = débit (ex. conversion).'),
                    Forms\Components\TextInput::make('points_before_transaction')
                        ->label('Solde avant')
                        ->required()
                        ->integer(),
                    Forms\Components\TextInput::make('points_after_transaction')
                        ->label('Solde après')
                        ->required()
                        ->integer()
                        ->rules([
                            fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                                $before = (int) $get('points_before_transaction');
                                $mov = (int) $get('points_earned');
                                if ((int) $value !== $before + $mov) {
                                    $fail('Le solde après doit valoir solde avant + points du mouvement ('.($before + $mov).').');
                                }
                            },
                        ]),
                    Forms\Components\TextInput::make('reference_id')
                        ->label('ID référence (course, filleul, etc.)')
                        ->numeric()
                        ->nullable(),
                    Forms\Components\Textarea::make('description')
                        ->label('Description affichable')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('user_avatar')
                    ->label('')
                    ->state(fn (LoyaltyHistory $record) => $record->user)
                    ->view('filament.tables.columns.user-avatar-hover')
                    ->grow(false),
                Tables\Columns\TextColumn::make('user_filament_name')
                    ->label('Utilisateur')
                    ->state(fn (LoyaltyHistory $record): string => $record->user?->getFilamentName() ?? '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $q) use ($search): void {
                            $like = '%'.$search.'%';
                            $q->where('firstname', 'like', $like)
                                ->orWhere('lastname', 'like', $like)
                                ->orWhere('name', 'like', $like)
                                ->orWhere('email', 'like', $like)
                                ->orWhere('phone', 'like', $like)
                                ->orWhere('username', 'like', $like);
                        });
                    }),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (LoyaltyTransactionType $state): string => $state->label())
                    ->color(fn (LoyaltyTransactionType $state): string => $state->color()),
                Tables\Columns\TextColumn::make('points_earned')->label('Δ pts')->sortable(),
                Tables\Columns\TextColumn::make('points_after_transaction')->label('Solde après')->sortable(),
                Tables\Columns\TextColumn::make('reference_id')->label('Réf.')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->label('Type')
                    ->options(collect(LoyaltyTransactionType::cases())->mapWithKeys(
                        fn (LoyaltyTransactionType $t) => [$t->value => $t->label()]
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
            'index' => Pages\ListLoyaltyHistories::route('/'),
            'create' => Pages\CreateLoyaltyHistory::route('/create'),
            'view' => Pages\ViewLoyaltyHistory::route('/{record}'),
            'edit' => Pages\EditLoyaltyHistory::route('/{record}/edit'),
        ];
    }
}
