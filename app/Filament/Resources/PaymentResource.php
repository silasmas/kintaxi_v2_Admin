<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Paiements';

    protected static ?string $modelLabel = 'Paiement';

    protected static ?string $pluralModelLabel = 'Paiements';

    protected static ?string $navigationGroup = 'Paiements';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('reference')->label('Référence'),
            TextEntry::make('amount')->label('Montant'),
            TextEntry::make('currency')->label('Devise'),
            TextEntry::make('channel')->label('Canal'),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Paiement')->schema([
                Forms\Components\Select::make('status_id')
                    ->label('Statut')
                    ->relationship('status', 'status_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => \App\Models\Status::formatShort($record->status_name ?? null))
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('reference')->label('Référence')->maxLength(45),
                Forms\Components\TextInput::make('provider_reference')->label('Réf. prestataire')->maxLength(45),
                Forms\Components\TextInput::make('phone')->label('Téléphone')->tel()->maxLength(45),
                Forms\Components\TextInput::make('amount_customer')->label('Montant client')->numeric()->step(0.01),
                Forms\Components\TextInput::make('amount')->label('Montant')->numeric()->step(0.01),
                Forms\Components\TextInput::make('currency')->label('Devise')->maxLength(45),
                Forms\Components\TextInput::make('channel')->label('Canal')->maxLength(45),
                Forms\Components\TextInput::make('gateway')->label('Passerelle (ID)')->numeric(),
                Forms\Components\Select::make('ride_id')
                    ->label('Course')
                    ->relationship('ride', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 'Course #' . $record->id)
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
                Tables\Columns\TextColumn::make('reference')->label('Référence')->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Montant')->numeric(decimalPlaces: 2)->sortable()->suffix(fn ($record) => ' ' . ($record->currency ?? 'CDF')),
                Tables\Columns\TextColumn::make('currency')->label('Devise'),
                Tables\Columns\TextColumn::make('channel')->label('Canal'),
                Tables\Columns\TextColumn::make('ride_id')->label('Course'),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
