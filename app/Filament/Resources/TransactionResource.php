<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?string $modelLabel = 'Transaction';

    protected static ?string $pluralModelLabel = 'Transactions';

    protected static ?string $navigationGroup = 'Paiements';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('type')->label('Type'),
            TextEntry::make('amount')->label('Montant'),
            TextEntry::make('user.email')->label('Utilisateur'),
            TextEntry::make('ride_id')->label('Course'),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction')->schema([
                Forms\Components\Select::make('status_id')
                    ->label('Statut')
                    ->relationship('status', 'status_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => \App\Models\Status::formatShort($record->status_name ?? null))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'email')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->phone ?? $record->id ?? '—'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('ride_id')
                    ->label('Course')
                    ->relationship('ride', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 'Course #' . $record->id)
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'deposit' => 'Dépôt',
                        'withdrawal' => 'Retrait',
                        'ride_payment' => 'Paiement course',
                        'commission' => 'Commission',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Montant (CDF)')
                    ->required()
                    ->numeric()
                    ->step(0.01),
                Forms\Components\TextInput::make('wallet_balance_before')->label('Solde avant')->numeric()->step(0.01),
                Forms\Components\TextInput::make('wallet_balance_after')->label('Solde après')->numeric()->step(0.01),
                Forms\Components\Select::make('payment_id')
                    ->label('Paiement')
                    ->relationship('payment', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 'Paiement #' . $record->id)
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
                Tables\Columns\TextColumn::make('user.email')->label('Utilisateur')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Type')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'deposit' => 'Dépôt',
                    'withdrawal' => 'Retrait',
                    'ride_payment' => 'Course',
                    'commission' => 'Commission',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('amount')->label('Montant')->money('CDF')->sortable(),
                Tables\Columns\TextColumn::make('ride_id')->label('Course'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'deposit' => 'Dépôt',
                    'withdrawal' => 'Retrait',
                    'ride_payment' => 'Paiement course',
                    'commission' => 'Commission',
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
