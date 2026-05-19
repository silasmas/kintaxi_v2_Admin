<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Resource Filament pour la gestion des avis.
 */
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
      ViewEntry::make('reviewer')
        ->label('Évaluateur')
        ->view('filament.infolists.entries.user-participant')
        ->columnSpan(1),
      ViewEntry::make('reviewee')
        ->label('Évalué')
        ->view('filament.infolists.entries.user-participant')
        ->columnSpan(1),
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
            ->getOptionLabelFromRecordUsing(fn ($record) => \App\Models\Status::formatShort($record->status_name ?? null))
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
            ->getOptionLabelFromRecordUsing(fn ($record) => 'Course #'.$record->id)
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
      ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['reviewer', 'reviewee']))
      ->columns([
        Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
        Tables\Columns\ViewColumn::make('reviewer')
          ->label('Évaluateur')
          ->view('filament.tables.columns.user-with-avatar-and-name')
          ->searchable(query: function (Builder $query, string $search): Builder {
            return $query->whereHas('reviewer', function (Builder $userQuery) use ($search): Builder {
              return $userQuery
                ->where('firstname', 'like', "%{$search}%")
                ->orWhere('lastname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
          }),
        Tables\Columns\ViewColumn::make('reviewee')
          ->label('Évalué')
          ->view('filament.tables.columns.user-with-avatar-and-name'),
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
