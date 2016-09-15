<?php

namespace Reversi;

use Reversi\Point;

class Coordinate extends Point
{
    public function __construct($coord)
    {
        if ($coord == null || mb_strlen($coord) != 2) {
            throw new \Exception("The arugment must be Reversi style coordinates!");
        }

        $x = ord($coord[0]) - ord('a') + 1;
        $y = $coord[1] - '1' + 1;
        parent::__construct($x, $y);
    }
}
