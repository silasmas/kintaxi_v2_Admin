<?php

namespace App\Filament\Concerns;

use App\Filament\Support\RecordNavigator;
use Illuminate\Database\Eloquent\Model;

/**
 * Ajoute la navigation précédent / suivant sur les pages ViewRecord Filament.
 */
trait NavigatesBetweenRecords
{
  /**
   * @return array{previous: ?Model, next: ?Model}
   */
  protected function getAdjacentRecords(): array
  {
    $query = static::getResource()::getEloquentQuery();

    return RecordNavigator::adjacent($this->getRecord(), $query);
  }

  /**
   * URL de la fiche précédente ou null.
   */
  protected function getPreviousRecordUrl(): ?string
  {
    $previous = $this->getAdjacentRecords()['previous'];
    if ($previous === null) {
      return null;
    }

    return static::getResource()::getUrl('view', ['record' => $previous]);
  }

  /**
   * URL de la fiche suivante ou null.
   */
  protected function getNextRecordUrl(): ?string
  {
    $next = $this->getAdjacentRecords()['next'];
    if ($next === null) {
      return null;
    }

    return static::getResource()::getUrl('view', ['record' => $next]);
  }

  /**
   * Données pour le composant Blade de navigation.
   *
   * @return array<string, mixed>
   */
  protected function buildNavigatorViewData(): array
  {
    $adjacent = $this->getAdjacentRecords();
    $previous = $adjacent['previous'] ? $this->prepareNavigatorRecord($adjacent['previous']) : null;
    $next = $adjacent['next'] ? $this->prepareNavigatorRecord($adjacent['next']) : null;

    return [
      'previous' => $previous,
      'next' => $next,
      'previousUrl' => $this->getPreviousRecordUrl(),
      'nextUrl' => $this->getNextRecordUrl(),
      'previousLabel' => $previous ? $this->formatNavigatorLabel($previous) : null,
      'nextLabel' => $next ? $this->formatNavigatorLabel($next) : null,
      'previousPreview' => $previous ? $this->formatNavigatorPreview($previous) : null,
      'nextPreview' => $next ? $this->formatNavigatorPreview($next) : null,
      'previousAvatars' => $previous ? $this->getNavigatorAvatars($previous) : [],
      'nextAvatars' => $next ? $this->getNavigatorAvatars($next) : [],
    ];
  }

  /**
   * Prépare l'enregistrement (eager load) avant affichage dans le navigateur.
   */
  protected function prepareNavigatorRecord(Model $record): Model
  {
    return $record;
  }

  /**
   * Avatars à afficher dans la carte précédent / suivant.
   *
   * @return array<int, array{user: \App\Models\User, role: string}>
   */
  protected function getNavigatorAvatars(Model $record): array
  {
    return [];
  }

  /**
   * Libellé court pour la carte de navigation.
   */
  protected function formatNavigatorLabel(Model $record): string
  {
    return (string) $record->getKey();
  }

  /**
   * Aperçu sous le libellé de navigation.
   */
  protected function formatNavigatorPreview(Model $record): ?string
  {
    return null;
  }
}
