<?php

namespace App\Filament\Widgets;

use App\Filament\Support\CurrencyFormatter;
use App\Models\Ride;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\WithPagination;

/**
 * Widget carte + liste paginée des courses sur le tableau de bord.
 */
class RidesMapWidget extends Widget
{
  use HasWidgetShield;
  use WithPagination;

  protected static string $view = 'filament.widgets.rides-map-widget';

  protected static ?int $sort = 3;

  protected int|string|array $columnSpan = 'full';

  protected static bool $isLazy = false;

  public const PER_PAGE = 10;

  protected string $paginationTheme = 'tailwind';

  public ?string $statusFilter = null;

  public string $search = '';

  public ?int $focusedRideId = null;

  protected static float $defaultLat = -4.4419;

  protected static float $defaultLng = 15.2663;

  /**
   * Nom de page Livewire pour éviter les conflits avec d'autres paginateurs.
   */
  protected function getPaginationPageName(): string
  {
    return 'ridesMapPage';
  }

  /**
   * Requête des courses avec filtres statut / recherche.
   */
  protected function ridesQuery(): Builder
  {
    $query = Ride::query()
      ->with(['passenger', 'driver', 'vehicle'])
      ->orderByDesc('created_at');

    if ($this->statusFilter) {
      $query->where('ride_status', $this->statusFilter);
    }

    $search = mb_strtolower(trim($this->search));
    if ($search !== '') {
      $query->where(function (Builder $inner) use ($search): void {
        $inner
          ->whereHas('passenger', function (Builder $userQuery) use ($search): void {
            $userQuery
              ->whereRaw('LOWER(firstname) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(lastname) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"]);
          })
          ->orWhereHas('driver', function (Builder $userQuery) use ($search): void {
            $userQuery
              ->whereRaw('LOWER(firstname) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(lastname) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
              ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"]);
          })
          ->orWhereHas('vehicle', function (Builder $vehicleQuery) use ($search): void {
            $vehicleQuery->whereRaw('LOWER(registration_number) LIKE ?', ["%{$search}%"]);
          })
          ->orWhereRaw('CAST(id AS CHAR) LIKE ?', ["%{$search}%"]);
      });
    }

    return $query;
  }

  /**
   * Courses paginées pour le tableau (10 par page).
   *
   * @return LengthAwarePaginator<int, array<string, mixed>>
   */
  public function getRidesPaginator(): LengthAwarePaginator
  {
    $paginator = $this->ridesQuery()->paginate(
      self::PER_PAGE,
      ['*'],
      $this->getPaginationPageName(),
    );

    $paginator->getCollection()->transform(function (Ride $ride, int $index) use ($paginator): array {
      $row = $this->mapRideToArray($ride);
      $row['numero'] = ($paginator->firstItem() ?? 1) + $index;

      return $row;
    });

    return $paginator;
  }

  public function updatedSearch(): void
  {
    $this->resetPage(pageName: $this->getPaginationPageName());
    $this->focusedRideId = null;
    $this->syncMapToFilters();
  }

  public function setStatusFilter(?string $status): void
  {
    $this->statusFilter = $status !== '' ? $status : null;
    $this->resetPage(pageName: $this->getPaginationPageName());
    $this->focusedRideId = null;
    $this->syncMapToFilters();
  }

  public function selectRide(int $rideId): void
  {
    $this->focusedRideId = $rideId;
    $this->syncMapToFilters();
  }

  /**
   * Met à jour la carte lors du changement de page du tableau.
   */
  public function updatedRidesMapPage(): void
  {
    $this->focusedRideId = null;
    $this->syncMapToFilters();
  }

  /**
   * Envoie les courses de la page courante au script carte.
   */
  protected function syncMapToFilters(): void
  {
    $this->dispatch(
      'rides-map-sync',
      mapId: $this->getMapElementId(),
      markers: $this->getFilteredMarkersForMap(),
      focusId: $this->focusedRideId,
    )->self();
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  public function getFilteredMarkersForMap(): array
  {
    return $this->getRidesPaginator()
      ->getCollection()
      ->values()
      ->all();
  }

  /**
   * Identifiant DOM stable de la carte pour ce widget Livewire.
   */
  public function getMapElementId(): string
  {
    return 'rides-map-'.str_replace('.', '-', $this->getId());
  }

  /**
   * @return array<string, mixed>
   */
  protected function getViewData(): array
  {
    $paginator = $this->getRidesPaginator();
    $pageRides = $paginator->getCollection()->values()->all();

    return [
      'mapId' => $this->getMapElementId(),
      'mapConfig' => [
        'defaultLat' => static::$defaultLat,
        'defaultLng' => static::$defaultLng,
        'markers' => $pageRides,
        'ridesToGeocode' => array_slice($this->getRidesToGeocode($pageRides), 0, 10),
      ],
      'statusOptions' => [
        '' => 'Toutes',
        'requested' => 'Demandées',
        'accepted' => 'Acceptées',
        'in_progress' => 'En cours',
        'completed' => 'Terminées',
        'canceled' => 'Annulées',
      ],
      'statusLabels' => [
        'requested' => 'Demandée',
        'accepted' => 'Acceptée',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
        'canceled' => 'Annulée',
      ],
      'ridesPaginator' => $paginator,
      'initialMarkers' => $pageRides,
    ];
  }

  /**
   * Transforme un modèle Ride en tableau pour la vue / la carte.
   *
   * @return array<string, mixed>
   */
  protected function mapRideToArray(Ride $ride): array
  {
    $start = $this->extractLatLng($ride->start_location) ?? $this->extractLatLng($ride->pickup_location);
    $end = $this->extractLatLng($ride->end_location);
    if ($start === null) {
      $start = $this->extractLatLng($ride->driver_location) ?? $this->extractLatLng($ride->pickup_data);
    }

    $passengerName = $ride->passenger?->getFilamentName() ?? '';
    $driverName = $ride->driver?->getFilamentName() ?? '';
    $vehiclePlate = $ride->vehicle?->registration_number ?? '';

    return [
      'id' => $ride->id,
      'start_location' => $ride->start_location,
      'end_location' => $ride->end_location,
      'pickup_location' => $ride->pickup_location,
      'start_lat' => $start[0] ?? null,
      'start_lng' => $start[1] ?? null,
      'end_lat' => $end[0] ?? null,
      'end_lng' => $end[1] ?? null,
      'ride_status' => $ride->ride_status,
      'cost' => $ride->cost,
      'cost_display' => CurrencyFormatter::formatUsd($ride->cost !== null ? (float) $ride->cost : null),
      'cost_tooltip' => CurrencyFormatter::formatTooltip($ride->cost !== null ? (float) $ride->cost : null),
      'passenger' => $this->serializeParticipant($ride->passenger),
      'driver' => $this->serializeParticipant($ride->driver),
      'passenger_name' => $passengerName,
      'driver_name' => $driverName,
      'vehicle_plate' => $vehiclePlate,
      'search_text' => mb_strtolower(trim("{$passengerName} {$driverName} {$vehiclePlate} #{$ride->id}")),
      'start_display' => $this->extractReadableAddress($ride->start_location ?? $ride->pickup_location ?? null),
      'end_display' => $this->extractReadableAddress($ride->end_location ?? null),
    ];
  }

  private function extractLatLng(mixed $raw): ?array
  {
    if (empty($raw)) {
      return null;
    }
    $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
    if (! is_array($decoded)) {
      return null;
    }
    $loc = $decoded['location'] ?? $decoded;
    $lat = $loc['lat'] ?? $loc['latitude'] ?? $decoded['lat'] ?? $decoded['latitude'] ?? null;
    $lng = $loc['lng'] ?? $loc['lon'] ?? $loc['longitude'] ?? $decoded['lng'] ?? $decoded['lon'] ?? $decoded['longitude'] ?? null;
    if ($lat !== null && $lng !== null) {
      return [(float) $lat, (float) $lng];
    }

    return null;
  }

  private function extractAddress(mixed $raw): string
  {
    if (empty($raw)) {
      return '';
    }
    if (is_string($raw)) {
      $decoded = json_decode($raw, true);
      if (is_array($decoded) && ! empty($decoded['description'])) {
        return trim((string) $decoded['description']);
      }

      return trim($raw);
    }
    if (is_array($raw) && ! empty($raw['description'])) {
      return trim((string) $raw['description']);
    }

    return '';
  }

  /**
   * @param  array<int, array<string, mixed>>  $rides
   * @return array<int, array<string, mixed>>
   */
  public function getRidesToGeocode(array $rides): array
  {
    $withoutBothCoords = array_filter($rides, function (array $ride): bool {
      $hasStart = $ride['start_lat'] !== null && $ride['start_lng'] !== null;
      $hasEnd = $ride['end_lat'] !== null && $ride['end_lng'] !== null;

      return ! $hasStart || ! $hasEnd;
    });

    return array_values(array_filter(array_map(function (array $ride): ?array {
      $startAddr = $this->extractAddress($ride['start_location'] ?? null) ?: $this->extractAddress($ride['pickup_location'] ?? null);
      $endAddr = $this->extractAddress($ride['end_location'] ?? null);
      if ($startAddr === '' && $endAddr === '') {
        return null;
      }

      return [
        'id' => $ride['id'],
        'start_address' => $startAddr,
        'end_address' => $endAddr,
      ];
    }, $withoutBothCoords)));
  }

  public function extractReadableAddress(mixed $raw): string
  {
    if (empty($raw)) {
      return '—';
    }
    if (is_string($raw)) {
      $decoded = json_decode($raw, true);
      if (! is_array($decoded)) {
        return $this->replaceCountryWithRdc(Str::limit(trim($raw), 40));
      }
    } else {
      $decoded = $raw;
    }
    $zone = $decoded['zone'] ?? $decoded['neighborhood'] ?? $decoded['quartier'] ?? null;
    $description = $decoded['description'] ?? $decoded['address'] ?? $decoded['formatted_address'] ?? null;
    $text = $zone && $description
      ? $zone.' – '.Str::limit($description, 40)
      : Str::limit(trim($zone ?? $description ?? json_encode($decoded)), 50);

    return $this->replaceCountryWithRdc($text ?: '—');
  }

  private function replaceCountryWithRdc(string $text): string
  {
    foreach (['République Démocratique du Congo', 'République democratique du Congo', 'Democratic Republic of the Congo'] as $variant) {
      $text = str_ireplace($variant, 'RDC', $text);
    }

    return preg_replace('/République\s+Démocratique\s+du\s+Congo/i', 'RDC', $text) ?? $text;
  }

  /**
   * Données participant pour affichage avatar + nom dans le tableau carte.
   *
   * @return array<string, mixed>|null
   */
  private function serializeParticipant(?User $user): ?array
  {
    if ($user === null) {
      return null;
    }

    return [
      'id' => $user->id,
      'name' => $user->getFilamentName(),
      'avatar_url' => $user->getFilamentAvatarUrl(),
      'initials' => $user->getFilamentInitials(),
    ];
  }

  public function updateRideStatus(int $rideId, string $status): void
  {
    $ride = Ride::find($rideId);
    if ($ride) {
      $ride->update(['ride_status' => $status]);
      Notification::make()->success()->title('Statut mis à jour')->send();
    }
  }

  public function deleteRide(int $rideId): void
  {
    $ride = Ride::find($rideId);
    if ($ride) {
      $ride->delete();
      Notification::make()->success()->title('Course supprimée')->send();
    }
  }
}
