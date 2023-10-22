<?php

namespace App\Enum;

use App\Enum\Contracts\Labelized;

enum WeightClass: string implements Labelized
{
    case WOMEN_STRAWWEIGHT = 'women_strawweight';
    case WOMEN_FLYWEIGHT = 'women_flyweight';
    case WOMEN_BANTAMWEIGHT = 'women_bantamweight';
    case WOMEN_FEATHERWEIGHT = 'women_featherweight';

    case STRAWWEIGHT = 'strawweight';
    case FLYWEIGHT = 'flyweight';
    case BANTAMWEIGHT = 'bantamweight';
    case FEATHERWEIGHT = 'featherweight';
    case LIGHTWEIGHT = 'lightweight';
    case WELTERWEIGHT = 'welterweight';
    case MIDDLEWEIGHT = 'middleweight';
    case LIGHT_HEAVYWEIGHT = 'light_heavyweight';
    case HEAVYWEIGHT = 'heavyweight';

    public function label(): string
    {
        return match ($this) {
            self::WOMEN_STRAWWEIGHT => 'Women\'s strawweight',
            self::WOMEN_FLYWEIGHT => 'Women\'s flyweight',
            self::WOMEN_BANTAMWEIGHT => 'Women\'s bantamweight',
            self::WOMEN_FEATHERWEIGHT => 'Women\'s featherweight',
            self::STRAWWEIGHT => 'Strawweight',
            self::FLYWEIGHT => 'Flyweight',
            self::BANTAMWEIGHT => 'Bantamweight',
            self::FEATHERWEIGHT => 'Featherweight',
            self::LIGHTWEIGHT => 'Lightweight',
            self::WELTERWEIGHT => 'Welterweight',
            self::MIDDLEWEIGHT => 'Middleweight',
            self::LIGHT_HEAVYWEIGHT => 'Light Heavyweight',
            self::HEAVYWEIGHT => 'Heavyweight',
        };
    }
}
