<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\VehicleResource;
use App\Models\Vehicle;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentVehiclesWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 2;

    protected static ?string $heading = 'Véhicules récents';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->with(['owner', 'category'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('Plaque')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mark')
                    ->label('Marque')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Modèle')
                    ->searchable(),
                Tables\Columns\ViewColumn::make('owner')
                    ->label('Propriétaire')
                    ->view('filament.tables.columns.owner-with-avatar'),
                Tables\Columns\TextColumn::make('category.category_name')
                    ->label('Catégorie')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Voir')
                    ->url(fn (Vehicle $record): string => VehicleResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
