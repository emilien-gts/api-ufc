<?php

namespace App\Enum\Fight;

use App\Enum\Contracts\Labelized;

enum Corner: string implements Labelized
{
    case RED = 'red';
    case BLUE = 'blue';

    public function label(): string
    {
        return match ($this) {
            self::RED => 'Red',
            self::BLUE => 'Blue'
        };
    }
}
