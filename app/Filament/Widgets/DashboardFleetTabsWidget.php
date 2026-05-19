<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\VehicleResource;
use App\Models\User;
use App\Models\Vehicle;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Widget dashboard avec onglets véhicules récents et chauffeurs mieux notés.
 */
class DashboardFleetTabsWidget extends Widget
{
  use HasWidgetShield;

  protected static string $view = 'filament.widgets.dashboard-fleet-tabs-widget';

  protected static ?int $sort = 2;

  protected int|string|array $columnSpan = 'full';

  public string $activeTab = 'vehicles';

  /**
   * @return array<int, array<string, mixed>>
   */
  public function getVehiclesData(): array
  {
    return Vehicle::query()
      ->with(['owner', 'category', 'status'])
      ->latest()
      ->limit(10)
      ->get()
      ->map(fn (Vehicle $vehicle): array => [
        'id' => $vehicle->id,
        'registration_number' => $vehicle->registration_number,
        'mark' => $vehicle->mark,
        'model' => $vehicle->model,
        'owner' => $vehicle->owner,
        'category' => $vehicle->category?->category_name,
        'created_at' => $vehicle->created_at?->format('d/m/Y H:i'),
        'view_url' => VehicleResource::getUrl('view', ['record' => $vehicle]),
      ])
      ->all();
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  public function getTopDriversData(): array
  {
    return User::query()
      ->with(['role'])
      ->whereHas('role', fn (Builder $query) => $query->where('role_name', 'like', '%chauff%'))
      ->where(function (Builder $query): void {
        $query->whereNotNull('rate')->where('rate', '>', 0);
      })
      ->orderByDesc('rate')
      ->limit(10)
      ->get()
      ->map(fn (User $user): array => [
        'id' => $user->id,
        'user' => $user,
        'rate' => $user->rate,
        'phone' => $user->phone,
        'view_url' => UserResource::getUrl('view', ['record' => $user]),
      ])
      ->all();
  }
}
