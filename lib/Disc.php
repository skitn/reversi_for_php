<?php

require_once './Point.php';

class Disc extends Point
{
    const COLOR_BLACK = 1;
    const COLOR_WHITE = -1;
    const COLOR_EMPTY = 0;
    const COLOR_WALL = 2;

    private $color;

    public function __construct($x = 0, $y = 0, $color)
    {
        parent::__construct($x, $y);
        if ($x == 0 && $y == 0) {
            $this->color = self::COLOR_EMPTY;
        } else {
            $this->color = $color;
        }
    }

    public function getColor()
    {
        return $this->color;
    }
}
