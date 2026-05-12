<?php

namespace App\Enums;

enum AppPreferenceType: string
{
    case Text = 'text';
    case Number = 'number';
    case MultipleChoice = 'multiple_choice';
    case Radio = 'radio';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Texte',
            self::Number => 'Nombre',
            self::MultipleChoice => 'Choix multiples',
            self::Radio => 'Radio (un choix)',
        };
    }
}
