<?php

namespace App\Models;

use App\Enums\AppPreferenceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppPreference extends Model
{
    protected $fillable = [
        'updated_by',
        'pref_key',
        'pref_value',
        'pref_name',
        'pref_description',
        'pref_type',
        'pref_expected_value',
    ];

    protected function casts(): array
    {
        return [
            'pref_type' => AppPreferenceType::class,
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Options issues de `pref_expected_value` (liste séparée par virgules).
     *
     * @return list<string>
     */
    public function expectedOptionsList(): array
    {
        if (blank($this->pref_expected_value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $this->pref_expected_value)
        )));
    }
}
