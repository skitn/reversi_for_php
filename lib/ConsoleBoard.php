<?php

namespace Reversi;

use Reversi\Board;
use Reversi\Disc;
use Reversi\Point;

class ConsoleBoard extends Board
{
    public function display()
    {
        echo "  abcdefgh  \n";
        for ($y = 1; $y <= parent::BOARD_SIZE + 2; $y++) {
            echo "  " + $y;
            for ($x = 1; $x <= parent::BOARD_SIZE + 2; $x++) {
                switch ($this->getColor(new Point($x, $y))) {
                    case Disc::COLOR_BLACK:
                        echo "●";
                        break;
                    case Disc::COLOR_WHITE:
                        echo "○";
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
