<?php

namespace Reversi;

use Reversi\Board;
use Reversi\Disc;
use Reversi\Point;

class ConsoleBoard extends Board
{
    public function display()
    {
        echo "  a b c d e f  \n";
        for ($y = 1; $y <= parent::BOARD_SIZE; $y++) {
            echo "  " + $y;
            for ($x = 1; $x <= parent::BOARD_SIZE; $x++) {
                switch ($this->getColor(new Point($x, $y))) {
                    case Disc::COLOR_BLACK:
                        echo " o";
                        break;
                    case Disc::COLOR_WHITE:
                        echo " x";
                        break;
                    default:
                        echo "  ";
                        break;
                }
            }
            echo "\n";
        }
    }
}
