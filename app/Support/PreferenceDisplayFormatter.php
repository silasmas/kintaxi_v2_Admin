<?php

namespace App\Support;

final class PreferenceDisplayFormatter
{
    /**
     * Remplace le symbole dollar par le mot pour l’affichage client (titres / descriptions).
     */
    public static function formatHumanText(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        return str_replace('$', ' Dollar', $text);
    }
}
