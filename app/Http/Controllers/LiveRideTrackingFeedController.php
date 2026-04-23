<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveRideTrackingFeedController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $allowedStatuses = ['accepted', 'in_progress', 'requested', 'completed', 'canceled'];
        $status = (string) $request->query('status', 'active');
        $search = trim((string) $request->query('q', ''));

        $query = Ride::query();

        if ($status === 'active') {
            $query->whereIn('ride_status', ['accepted', 'in_progress']);
        } elseif (in_array($status, $allowedStatuses, true)) {
            $query->where('ride_status', $status);
        }

        if ($search !== '') {
            $query->where(function ($inner) use ($search): void {
                if (ctype_digit($search)) {
                    $inner->where('id', (int) $search);
                } else {
                    $inner->where('ride_status', 'like', "%{$search}%");
                }
            });
        }

        $rides = $query
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get()
            ->map(function (Ride $ride): array {
                $driverPoint = $this->extractPoint($ride->driver_location);
                $startPoint = $this->extractPoint($ride->start_location) ?? $this->extractPoint($ride->pickup_location);
                $endPoint = $this->extractPoint($ride->end_location);

                return [
                    'id' => $ride->id,
                    'ride_status' => $ride->ride_status,
                    'driver_lat' => $driverPoint[0] ?? null,
                    'driver_lng' => $driverPoint[1] ?? null,
                    'start_lat' => $startPoint[0] ?? null,
                    'start_lng' => $startPoint[1] ?? null,
                    'end_lat' => $endPoint[0] ?? null,
                    'end_lng' => $endPoint[1] ?? null,
                    'updated_at' => optional($ride->updated_at)->toIso8601String(),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'default_lat' => -4.325,
            'default_lng' => 15.322,
            'rides' => $rides,
        ]);
    }

    private function extractPoint(mixed $raw): ?array
    {
        if (empty($raw)) {
            return null;
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        if (! is_array($decoded)) {
            return null;
        }

        $loc = $decoded['location'] ?? $decoded;
        $lat = $loc['lat'] ?? $loc['latitude'] ?? null;
        $lng = $loc['lng'] ?? $loc['lon'] ?? $loc['longitude'] ?? null;

        if ($lat === null || $lng === null) {
            return null;
        }

        return [(float) $lat, (float) $lng];
    }
}
