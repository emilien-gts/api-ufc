<?php

namespace App\Model;

class FeetAndInchesToCentimeters
{
    public function transform(?string $input): ?int
    {
        if (empty($input)) {
            return null;
        }

        if (\preg_match('/(\d+)\' (\d+)"/', $input, $matches)) { // (format: "X' Y"")
            $feet = (int) $matches[1];
            $inches = (int) $matches[2];

            $centimetres = ($feet * 30.48) + ($inches * 2.54);

            return \intval(\floor($centimetres));
        }

        if (\preg_match('/^(\d+(?:\.\d+)?)\s*("|inches?|")?$/i', $input, $matches)) { // (format: Z")
            $inches = $matches[1];
            $centimetres = \intval($inches) * 2.54;

            return \intval(\floor($centimetres));
        }

        return null;
    }
}
