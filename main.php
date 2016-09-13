<?php

require_once './vendor/autoload.php';
use Reversi\ConsoleBoard;
use Reversi\Coordinate;

$board = new ConsoleBoard();
$coordinate = new Coordinate('c2');

while (true) {
    $board->display();   
    $coord = fgets(STDIN); 
    break; 
}
