<?php

namespace App\Filament\Widgets;

use App\Models\Ride;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RidesMapWidget extends Widget
{
    use HasWidgetShield;
    protected static string $view = 'filament.widgets.rides-map-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    /** Centre par défaut : Kinshasa */
    protected static float $defaultLat = -4.4419;

    protected static float $defaultLng = 15.2663;

    /**
     * Extrait lat/lng d'un champ (JSON ou objet avec location.lat/lng).
     */
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

    /**
     * Dernières courses avec coordonnées départ et arrivée quand disponibles.
     *
     * @return array<int, array{id: int, start_location: ?string, end_location: ?string, start_lat: ?float, start_lng: ?float, end_lat: ?float, end_lng: ?float, ride_status: string, cost: ?float}>
     */
    public function getRidesWithCoordinates(): array
    {
        $rides = Ride::query()
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return $rides->map(function (Ride $ride) {
            $start = $this->extractLatLng($ride->start_location) ?? $this->extractLatLng($ride->pickup_location);
            $end = $this->extractLatLng($ride->end_location);
            if ($start === null) {
                $start = $this->extractLatLng($ride->driver_location) ?? $this->extractLatLng($ride->pickup_data);
            }

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
            ];
        })->values()->all();
    }

    /**
     * Extrait une adresse texte depuis un champ (JSON avec "description" ou chaîne brute).
     */
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
     * Courses sans coordonnées mais avec adresses à géocoder (départ et/ou arrivée).
     *
     * @return array<int, array{id: int, start_address: string, end_address: string, start_location: ?string, end_location: ?string, ride_status: string, cost: ?float}>
     */
    public function getRidesToGeocode(): array
    {
        $rides = $this->getRidesWithCoordinates();
        $withoutBothCoords = array_filter($rides, function ($r) {
            $hasStart = $r['start_lat'] !== null && $r['start_lng'] !== null;
            $hasEnd = $r['end_lat'] !== null && $r['end_lng'] !== null;
            return ! $hasStart || ! $hasEnd;
        });

        return array_values(array_filter(array_map(function ($r) {
            $startAddr = $this->extractAddress($r['start_location'] ?? null) ?: $this->extractAddress($r['pickup_location'] ?? null);
            $endAddr = $this->extractAddress($r['end_location'] ?? null);
            if ($startAddr === '' && $endAddr === '') {
                return null;
            }
            return [
                'id' => $r['id'],
                'start_address' => $startAddr,
                'end_address' => $endAddr,
                'start_location' => $r['start_location'],
                'end_location' => $r['end_location'],
                'ride_status' => $r['ride_status'],
                'cost' => $r['cost'],
            ];
        }, $withoutBothCoords)));
    }

    /**
     * Extrait une adresse lisible (zone, quartier, description) depuis un champ JSON.
     */
    public function extractReadableAddress(mixed $raw): string
    {
        if (empty($raw)) {
            return '—';
        }
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                return $this->replaceCountryWithRdc(\Illuminate\Support\Str::limit(trim($raw), 40));
            }
        } else {
            $decoded = $raw;
        }
        $zone = $decoded['zone'] ?? $decoded['neighborhood'] ?? $decoded['quartier'] ?? null;
        $description = $decoded['description'] ?? $decoded['address'] ?? $decoded['formatted_address'] ?? null;
        $raw = $zone && $description
            ? $zone . ' – ' . \Illuminate\Support\Str::limit($description, 40)
            : \Illuminate\Support\Str::limit(trim($zone ?? $description ?? json_encode($decoded)), 50);
        $result = $raw ?: '—';

        return $this->replaceCountryWithRdc($result);
    }

    /**
     * Remplace le nom complet du pays par « RDC » quand c'est la République Démocratique du Congo.
     */
    private function replaceCountryWithRdc(string $text): string
    {
        $rdcVariants = [
            'République Démocratique du Congo',
            'République democratique du Congo',
            'Democratic Republic of the Congo',
        ];
        foreach ($rdcVariants as $variant) {
            $text = str_ireplace($variant, 'RDC', $text);
        }
        $text = preg_replace('/République\s+Démocratique\s+du\s+Congo/i', 'RDC', $text);

        return $text;
    }

    public function getViewData(): array
    {
        $rides = $this->getRidesWithCoordinates();
        $ridesToGeocode = array_slice($this->getRidesToGeocode(), 0, 15);
        $defaultLat = static::$defaultLat;
        $defaultLng = static::$defaultLng;

        $ridesForTable = array_map(function ($ride, $index) {
            return [
                'id' => $ride['id'],
                'numero' => $index + 1,
                'start_display' => $this->extractReadableAddress($ride['start_location'] ?? $ride['pickup_location'] ?? null),
                'end_display' => $this->extractReadableAddress($ride['end_location'] ?? null),
                'ride_status' => $ride['ride_status'],
                'cost' => $ride['cost'],
            ];
        }, array_slice($rides, 0, 10), array_keys(array_slice($rides, 0, 10)));

        return [
            'rides' => $rides,
            'ridesForTable' => $ridesForTable,
            'markers' => array_values($rides),
            'ridesToGeocode' => $ridesToGeocode,
            'defaultLat' => $defaultLat,
            'defaultLng' => $defaultLng,
        ];
    }

    public function updateRideStatus(int $rideId, string $status): void
    {
        $ride = Ride::find($rideId);
        if ($ride) {
            $ride->update(['ride_status' => $status]);
            Notification::make()
                ->success()
                ->title('Statut mis à jour')
                ->send();
        }
    }

    public function deleteRide(int $rideId): void
    {
        $ride = Ride::find($rideId);
        if ($ride) {
            $ride->delete();
            Notification::make()
                ->success()
                ->title('Course supprimée')
                ->send();
        }
    }
}
