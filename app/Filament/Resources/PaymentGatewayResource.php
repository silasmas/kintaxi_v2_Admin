<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewayResource\Pages;
use App\Models\PaymentGateway;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Passerelles de paiement';

    protected static ?string $modelLabel = 'Passerelle de paiement';

    protected static ?string $pluralModelLabel = 'Passerelles de paiement';

    protected static ?string $navigationGroup = 'Paiements';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Passerelle de paiement')->schema([
                Forms\Components\Select::make('status_id')
                    ->label('Statut')
                    ->relationship('status', 'status_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => \App\Models\Status::formatShort($record->status_name ?? null))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('gateway_name')
                    ->label('Nom de la passerelle')
                    ->required()
                    ->maxLength(255),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('gateway_name')->label('Nom')->searchable()->sortable(),
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
            'index' => Pages\ListPaymentGateways::route('/'),
            'create' => Pages\CreatePaymentGateway::route('/create'),
            'view' => Pages\ViewPaymentGateway::route('/{record}'),
            'edit' => Pages\EditPaymentGateway::route('/{record}/edit'),
        ];
    }
}
