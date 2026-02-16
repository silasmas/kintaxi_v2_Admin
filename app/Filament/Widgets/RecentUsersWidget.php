<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentUsersWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    protected static ?string $heading = 'Utilisateurs récents';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->with(['role'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('firstname')
                    ->label('Nom')
                    ->formatStateUsing(fn (?string $state, User $record): string => trim($record->firstname . ' ' . $record->lastname) ?: ($record->email ?? $record->username ?? '—'))
                    ->searchable(['firstname', 'lastname', 'email']),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('role.role_name')
                    ->label('Rôle')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Voir')
                    ->url(fn (User $record): string => UserResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
