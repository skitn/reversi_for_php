<?php

namespace Reversi;

class Point
{
    private $x;
    private $y;

    public function __construct($x = 0, $y = 0)
    {
        $this->x = $x;    
        $this->y = $y;    
    }

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }

    public function setX($x)
    {
        $this->x = $x;
    }

    public function setY($y)
    {
        $this->y = $y;
    }
}
