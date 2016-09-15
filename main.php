<?php

require_once './vendor/autoload.php';
use Reversi\ConsoleBoard;
use Reversi\Coordinate;
use Reversi\Disc;

$board = new ConsoleBoard();

while (true) {
    $board->display();
    echo "black:" . $board->countDisc(Disc::COLOR_BLACK) . " ";
    echo "white:" . $board->countDisc(Disc::COLOR_WHITE) . " ";
    echo "empty:" . $board->countDisc(Disc::COLOR_EMPTY) . "\n\n";
    echo "手を入力してくだされ:";

    $coord = trim(fgets(STDIN));

    if ($coord == "p") {
        if (!$board->pass()) {
            echo "passできません\n";
        }
        continue;
    }

    if ($coord == "u") {
        $board->undo();
        continue;
    }

    try {
        $p = new Coordinate($coord);
    } catch(\Exception $e) {
        echo "リバーシ形式の手を入力してください\n";
        continue;
    }

    if ($board->move($p) == false) {
        echo "そこには置けません\n";
        continue;
    }

    if ($board->isGameOver()) {
        echo "ゲーム終了ーーー\n";
        return;
    }
}
