<?php

namespace App\Model;

class LbsToKilograms
{
    public function transform(?string $input): ?int
    {
        if (empty($input)) {
            return null;
        }

        if (\preg_match('/^[0-9]+$/', $input)) {
            $lbs = (int) $input;

            return \intval(\floor($lbs * 0.45359237));
        }

        return null;
    }
}
