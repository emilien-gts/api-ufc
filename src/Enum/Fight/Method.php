<?php

namespace App\Enum\Fight;

use App\Enum\Contracts\Labelized;

enum Method: string implements Labelized
{
    case KO_TKO = 'ko_tko';
    case SUBMISSION = 'submission';
    case UNANIMOUS_DECISION = 'unanimous_decision';
    case SPLIT_DECISION = 'split_decision';
    case MAJORITY_DECISION = 'majority_decision';
    case UNANIMOUS_DRAW = 'unanimous_draw';
    case SPLIT_DRAW = 'split_draw';
    case MAJORITY_DRAW = 'majority_draw';
    case NO_CONTEST = 'no_contest';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::KO_TKO => 'KO/TKO',
            self::SUBMISSION => 'Submission',
            self::UNANIMOUS_DECISION => 'Unanimous Decision',
            self::SPLIT_DECISION => 'Split Decision',
            self::MAJORITY_DECISION => 'Majority Decision',
            self::UNANIMOUS_DRAW => 'Unanimous Draw',
            self::SPLIT_DRAW => 'Split Draw',
            self::MAJORITY_DRAW => 'Majority Draw',
            self::NO_CONTEST => 'No Contest',
            self::OTHER => 'Other'
        };
    }

    public static function tryFromUfcStats(string $value): Method
    {
        return match ($value) {
            'KO/TKO' => self::KO_TKO,
            'Submission' => self::SUBMISSION,
            'Decision - Unanimous' => self::UNANIMOUS_DECISION,
            'Decision - Split' => self::SPLIT_DECISION,
            'Decision - Majority' => self::MAJORITY_DECISION,
            default => self::OTHER
        };
    }
}
