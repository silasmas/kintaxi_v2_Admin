<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Avis';

    protected static ?string $modelLabel = 'Avis';

    protected static ?string $pluralModelLabel = 'Avis';

    protected static ?string $navigationGroup = 'Courses';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('rating')->label('Note'),
            TextEntry::make('reviewer.email')->label('Évaluateur'),
            TextEntry::make('reviewee.email')->label('Évalué'),
            TextEntry::make('comment')->label('Commentaire')->columnSpanFull(),
        ])->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Avis')->schema([
                Forms\Components\Select::make('status_id')
                    ->label('Statut')
                    ->relationship('status', 'status_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->status_name ?? $record->id ?? '—'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('reviewer_id')
                    ->label('Évaluateur')
                    ->relationship('reviewer', 'email')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('reviewee_id')
                    ->label('Évalué')
                    ->relationship('reviewee', 'email')
                    ->getOptionLabelFromRecordUsing(fn ($record) => (string) ($record->email ?? $record->id ?? '—'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('ride_id')
                    ->label('Course')
                    ->relationship('ride', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 'Course #' . $record->id)
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('rating')
                    ->label('Note (1-5)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5),
                Forms\Components\Textarea::make('comment')->label('Commentaire')->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('reviewer.email')->label('Évaluateur')->searchable(),
                Tables\Columns\TextColumn::make('reviewee.email')->label('Évalué'),
                Tables\Columns\TextColumn::make('ride_id')->label('Course'),
                Tables\Columns\TextColumn::make('rating')->label('Note')->badge()->color(fn ($state) => match (true) {
                    $state >= 4 => 'success',
                    $state >= 3 => 'warning',
                    default => 'danger',
                }),
                Tables\Columns\TextColumn::make('comment')->label('Commentaire')->limit(40),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y')->sortable(),
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
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
