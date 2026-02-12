<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status_id',
        'role_id',
        'firstname',
        'lastname',
        'surname',
        'username',
        'phone',
        'gender',
        'birthdate',
        'country_code',
        'city',
        'address_1',
        'address_2',
        'p_o_box',
        'belongs_to',
        'api_token',
        'avatar_url',
        'session_socket_io',
        'fcm_token',
        'rate',
        'activation_otp',
        'wallet_balance',
        'loyalty_point',
        'current_vehicle_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'birthdate' => 'date',
            'password' => 'hashed',
            'wallet_balance' => 'float',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentName(): string
    {
        $name = trim($this->firstname . ' ' . $this->lastname);
        if ($name !== '') {
            return $name;
        }
        if (filled($this->name)) {
            return (string) $this->name;
        }
        if (filled($this->email)) {
            return (string) $this->email;
        }
        if (filled($this->phone)) {
            return (string) $this->phone;
        }
        return 'Utilisateur';
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'role_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'belongs_to');
    }

    public function currentVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'current_vehicle_id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'user_id');
    }

    public function ridesAsPassenger(): HasMany
    {
        return $this->hasMany(Ride::class, 'passenger_id');
    }

    public function ridesAsDriver(): HasMany
    {
        return $this->hasMany(Ride::class, 'driver_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function notificationsSent(): HasMany
    {
        return $this->hasMany(AppNotification::class, 'notification_from');
    }

    public function notificationsReceived(): HasMany
    {
        return $this->hasMany(AppNotification::class, 'notification_to');
    }
}
