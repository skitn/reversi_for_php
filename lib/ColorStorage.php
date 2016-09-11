<?php

namespace Reversi;

class ColorStorage
{
    private $data = [];
    public function get($color)
    {
        return $this->data[$color + 1];
    }

    public function set($color, $value)
    {
        $this->data[$color + 1] = $value;
    }
}
