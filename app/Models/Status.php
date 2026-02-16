<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Status extends Model
{
    protected $table = 'status';

    protected $fillable = [
        'created_by',
        'status_name',
        'status_description',
        'icon',
        'color',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Formate le nom du statut pour l'affichage : premier mot avant « / », première lettre en majuscule.
     * Ex. : « créé/en attente de confirmation » → « Créé »
     */
    public static function formatShort(?string $name): string
    {
        if ($name === null || $name === '') {
            return '—';
        }
        $first = trim(explode('/', $name)[0] ?? '');
        if ($first === '') {
            return $name;
        }

        return mb_strtoupper(mb_substr($first, 0, 1)) . mb_substr($first, 1);
    }
}
