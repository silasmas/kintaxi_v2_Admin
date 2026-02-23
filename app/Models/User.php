<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
    use HasFactory, HasRoles, Notifiable;

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

    public function getFilamentAvatarUrl(): ?string
    {
        if (empty($this->avatar_url)) {
            return null;
        }

        $url = $this->avatar_url;
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = asset(ltrim($url, '/'));
        }

        // Cache busting : forcer le rechargement après mise à jour du profil
        $separator = str_contains($url, '?') ? '&' : '?';
        $url .= $separator . 'v=' . ($this->updated_at?->timestamp ?? time());

        return $url;
    }

    /**
     * Retourne les initiales du prénom et nom (ex: "Jean Dupont" → "JD").
     */
    public function getFilamentInitials(): string
    {
        $name = trim($this->firstname . ' ' . $this->lastname);
        if ($name !== '') {
            $parts = preg_split('/\s+/', $name, 2);
            $initials = '';
            foreach (array_slice($parts, 0, 2) as $part) {
                $initials .= mb_substr($part, 0, 1);
            }
            return strtoupper($initials) ?: '?';
        }
        if (filled($this->email)) {
            return strtoupper(mb_substr($this->email, 0, 1));
        }
        if (filled($this->username)) {
            return strtoupper(mb_substr($this->username, 0, 1));
        }
        if (filled($this->phone)) {
            return strtoupper(mb_substr($this->phone, 0, 1));
        }
        return '?';
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
