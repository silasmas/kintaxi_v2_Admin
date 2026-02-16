<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Méthodes de paiement';

    protected static ?string $modelLabel = 'Méthode de paiement';

    protected static ?string $pluralModelLabel = 'Méthodes de paiement';

    protected static ?string $navigationGroup = 'Paiements';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('method_name')->label('Nom'),
            TextEntry::make('paymentGateway.gateway_name')->label('Passerelle'),
            TextEntry::make('status.status_name')
                ->label('Statut')
                ->formatStateUsing(fn (?string $state): string => \App\Models\Status::formatShort($state)),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Méthode de paiement')->schema([
                Forms\Components\Select::make('status_id')
                    ->label('Statut')
                    ->relationship('status', 'status_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => \App\Models\Status::formatShort($record->status_name ?? null))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('method_name')
                    ->label('Nom de la méthode')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('payment_gateway_id')
                    ->label('Passerelle')
                    ->relationship('paymentGateway', 'gateway_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->gateway_name ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('method_name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('paymentGateway.gateway_name')->label('Passerelle'),
                Tables\Columns\TextColumn::make('status.status_name')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => \App\Models\Status::formatShort($state)),
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'view' => Pages\ViewPaymentMethod::route('/{record}'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
