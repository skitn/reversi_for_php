<?php

require_once "./Point.php";
require_once "./Disc.php";
require_once "./ColorStorage.php";

class Board
{
    const BOARD_SIZE = 6;
    const MAX_TURNS = 32;

    // 方向
    const NONE         = 0;
    const UPPER        = 1;
    const UPPER_LEFT   = 2;
    const LEFT         = 4;
    const LOWER_LEFT   = 8;
    const LOWER        = 16;
    const LOWER_RIGHT  = 32;
    const RIGHT        = 64;
    const UPPER_RIGHT  = 128;
    
    // 盤
    private $raw_board;
    // ターン数
    private $turns;
    // 現在のプレーヤー
    private $current_color;

    // 変更点の記録
    private $update_log;

    private $movable_dir = [];

    private $movable_pos;

    // 各色の石の数
    private $discs;

    public function __construct()
    {
        $raw_board = array_fill(0, self::BOARD_SIZE + 2, 0);
        foreach ($raw_board as &$board) {
            $board = array_fill(0, self::BOARD_SIZE + 2, 0);
        }
        $this->raw_board = $raw_board;
        $this->discs = new ColorStorage();

        for ($i = 0; $i <= self::MAX_TURNS; $i++) {
            $this->movable_pos[$i] = [];
        }

        $this->init();
    }

    public function checkMobility(Disc $disc)
    {
        $x = $disc->getX();
        $y = $disc->getY();

        $dir = self::NONE;

        if ($this->raw_board[$x][$y] != Disc::COLOR_EMPTY) {
            return self::NONE;
        }

        // 上
        if ($this->raw_board[$x][$y - 1] == -$disc->getColor()) {
            $s_x = $disc->getX();
            $s_y = $disc->getY() - 2;
            while ($this->raw_board[$s_x][$s_y] == -$disc->getColor()) { $s_y--; }
            if ($this->raw_board[$s_x][$s_y] == $disc->getColor()) {
                $dir |= self::UPPER;
            }
        }

        // 下
        if ($this->raw_board[$x][$y + 1] == -$disc->getColor()) {
            $s_x = $disc->getX();
            $s_y = $disc->getY() + 2;
            while ($this->raw_board[$s_x][$s_y] == -$disc->getColor()) { $s_y++; }
            if ($this->raw_board[$s_x][$s_y] == $disc->getColor()) {
                $dir |= self::LOWER;
            }
        }

        // 左
        if ($this->raw_board[$x - 1][$y] == -$disc->getColor()) {
            $s_x = $disc->getX() - 2;
            $s_y = $disc->getY();
            while ($this->raw_board[$s_x][$s_y] == -$disc->getColor()) { $s_x--; }
            if ($this->raw_board[$s_x][$s_y] == $disc->getColor()) {
                $dir |= self::LEFT;
            }
        }

        // 右
        if ($this->raw_board[$x + 1][$y] == -$disc->getColor()) {
            $s_x = $disc->getX() + 2;
            $s_y = $disc->getY();
            while ($this->raw_board[$s_x][$s_y] == -$disc->getColor()) { $s_x++; }
            if ($this->raw_board[$s_x][$s_y] == $disc->getColor()) {
                $dir |= self::RIGHT;
            }
        }

        // 右上
        if ($this->raw_board[$x + 1][$y - 1] == -$disc->getColor()) {
            $s_x = $disc->getX() + 2;
            $s_y = $disc->getY() - 2;
            while ($this->raw_board[$s_x][$s_y] == -$disc->getColor()) { $s_x++; $s_y--; }
            if ($this->raw_board[$s_x][$s_y] == $disc->getColor()) {
                $dir |= self::UPPER_RIGHT;
            }
        }

        // 左上
        if ($this->raw_board[$x - 1][$y - 1] == -$disc->getColor()) {
            $s_x = $disc->getX() - 2;
            $s_y = $disc->getY() - 2;
            while ($this->raw_board[$s_x][$s_y] == -$disc->getColor()) { $s_x--; $s_y--; }
            if ($this->raw_board[$s_x][$s_y] == $disc->getColor()) {
                $dir |= self::UPPER_LEFT;
            }
        }

        // 左下
        if ($this->raw_board[$x - 1][$y + 1] == -$disc->getColor()) {
            $s_x = $disc->getX() - 2;
            $s_y = $disc->getY() + 2;
            while ($this->raw_board[$s_x][$s_y] == -$disc->getColor()) { $s_x--; $s_y++; }
            if ($this->raw_board[$s_x][$s_y] == $disc->getColor()) {
                $dir |= self::LOWER_LEFT;
            }
        }

        // 右下
        if ($this->raw_board[$x + 1][$y + 1] == -$disc->getColor()) {
            $s_x = $disc->getX() + 2;
            $s_y = $disc->getY() + 2;
            while ($this->raw_board[$s_x][$s_y] == -$disc->getColor()) { $s_x++; $s_y++; }
            if ($this->raw_board[$s_x][$s_y] == $disc->getColor()) {
                $dir |= self::LOWER_RIGHT;
            }
        }

        return $dir;
    }

    public function move(Point $point)
    {
        if ($point->getX() < 0 || $point->getX() >= self::BOARD_SIZE) {
            return false;
        }
        if ($point->getY() < 0 || $point->getY() >= self::BOARD_SIZE) {
            return false;
        }

        if ($this->movable_dir[$this->turns][$point->getX()][$point->getY()] == self::NONE) {
            return false;
        }

        $this->flipDiscs($point);

        $this->turns++;
        $this->current_color = -$this->current_color;

        $this->initMovable();

        return true;
    }

    private function initMovable()
    {
        $this->movable_pos[$this->turns] = null;
        
        for ($y = 0; $y < self::BOARD_SIZE; $y++) {
            for ($x = 0; $x < self::BOARD_SIZE; $x++) {
                $disc = new Disc($x, $y, $this->current_color);
                $dir = $this->checkMobility($disc);
                if ($dir != self::NONE) {
                    $this->movable_pos[$this->turns][] = $disc;
                }
                $this->movable_dir[$this->turns][$x][$y] = $dir;
            }
        }
    }

    private function flipDiscs(Point $point)
    {
        $dir = $this->movable_dir[$this->turns][$point->getX()][$point->getY()];

        $update = [];

        $raw_board[$point->getX()][$point->getY()] = $this->current_color;
        $update[] = new Disc($point->getX(), $point->getY(), $this->current_color);

        // 上
        if (($dir & self::UPPER) != self::NONE) {
            $y = $point->getY();
            while ($this->raw_board[$point->getX()][--$y] != $this->current_color) {
                $this->raw_board[$point->getX()][$y] = $this->current_color;
                $update[] = new Disc($point->getX(), $y, $this->current_color);
            }
        }

        // 下
        if (($dir & self::LOWER) != self::NONE) {
            $y = $point->getY();
            while ($this->raw_board[$point->getX()][++$y] != $this->current_color) {
                $this->raw_board[$point->getX()][$y] = $this->current_color;
                $update[] = new Disc($point->getX(), $y, $this->current_color);
            }
        }

        // 左 
        if (($dir & self::LEFT) != self::NONE) {
            $x = $point->getX();
            while ($this->raw_board[--$x][$point->getY()] != $this->current_color) {
                $this->raw_board[$x][$point->getY()] = $this->current_color;
                $update[] = new Disc($x, $point->getY(), $this->current_color);
            }
        }

        // 右 
        if (($dir & self::RIGHT) != self::NONE) {
            $x = $point->getX();
            while ($this->raw_board[++$x][$point->getY()] != $this->current_color) {
                $this->raw_board[$x][$point->getY()] = $this->current_color;
                $update[] = new Disc($x, $point->getY(), $this->current_color);
            }
        }

        // 右上
        if (($dir & self::UPPER_RIGHT) != self::NONE) {
            $x = $point->getX();
            $y = $point->getY();
            while ($this->raw_board[++$x][--$y] != $this->current_color) {
                $this->raw_board[$x][$y] = $this->current_color;
                $update[] = new Disc($x, $y, $this->current_color);
            }
        }

        // 左上
        if (($dir & self::UPPER_LEFT) != self::NONE) {
            $x = $point->getX();
            $y = $point->getY();
            while ($this->raw_board[--$x][--$y] != $this->current_color) {
                $this->raw_board[$x][$y] = $this->current_color;
                $update[] = new Disc($x, $y, $this->current_color);
            }
        }

        // 左下
        if (($dir & self::LOWER_LEFT) != self::NONE) {
            $x = $point->getX();
            $y = $point->getY();
            while ($this->raw_board[--$x][++$y] != $this->current_color) {
                $this->raw_board[$x][$y] = $this->current_color;
                $update[] = new Disc($x, $y, $this->current_color);
            }
        }

        // 右下
        if (($dir & self::LOWER_RIGHT) != self::NONE) {
            $x = $point->getX();
            $y = $point->getY();
            while ($this->raw_board[++$x][++$y] != $this->current_color) {
                $this->raw_board[$x][$y] = $this->current_color;
                $update[] = new Disc($x, $y, $this->current_color);
            }
        }

        $disc_diff = count($update);

        $this->discs->set($this->current_color, $this->discs->get($this->current_color) + $disc_diff);
        $this->discs->set(-$this->current_color, $this->discs->get(-$this->current_color) - ($disc_diff - 1));
        $this->discs->set(Disc::COLOR_EMPTY, $this->discs->get(Disc::COLOR_EMPTY) - 1);

        $this->update_log[] = $update;
    }

    public function isGameOver()
    {
        // 最大ターン数超えたら終了
        if ($this->turns == self::MAX_TURNS) {
            return true;
        }

        // 打てる手があるならゲーム終了ではない
        if (count($this->movable_pos[$this->turns]) != 0) {
            return false;
        }

        // 現在の手番と逆の色が打てるかどうか調べる
        $disc = new Disc(0, 0, -$this->current_color);
        for ($x = 1; $x <= self::BOARD_SIZE; $x++) {
            $disc->setX($x);
            for ($y = 1; $y <= self::BOARD_SIZE; $y++) {
                $disc->setY($y);
                // 置ける箇所が一つでもあるなら続行
                if ($this->checkMobility($disc) != self::NONE) {
                    return false;
                }
            }
        }

        return true;
    }

    public function pass()
    {
        // 打てる手があるならゲーム終了ではない
        if (count($this->movable_pos[$this->turns]) != 0) {
            return false;
        }

        if ($this->isGameOver()) {
            return false;
        }
        
        $this->current_color = -$this->current_color;
        
        $this->update_log[] = [];

        $this->initMovable();

        return true;
    }

    public function undo()
    {
        if ($this->turns == 0) {
            return false;
        }

        $this->current_color = -$this->current_color;

        $update = $this->update_log[count($this->update_log) - 1];
        unset($this->update_log[count($this->update_log) - 1]);

        if (count($update) == 0) {
            $this->movable_pos[$this->turns] = null;
            for ($x = 1; $x <= self::BOARD_SIZE; $x++) {
                for ($x = 1; $x <= self::BOARD_SIZE; $x++) {
                    $this->movable_dir[$this->turns][$x][$y] = self::NONE;
                }
            }
        } else {
            $this->turns--;
            $p = $update[0];
            $this->raw_board[$p->getX()][$p->getY()] = Disc::COLOR_EMPTY;
            foreach($update as $point) {
                $this->raw_board[$point->getX()][$point->getY()] = -$this->current_color;
            }

            $disc_diff = count($update);
            $this->discs->set($this->current_color, $this->discs->get($this->current_color) - $disc_diff);
            $this->discs->set(-$this->current_color, $this->discs->get(-$this->current_color) + ($disc_diff - 1));
            $this->discs->set(Disc::COLOR_EMPTY, $this->discs->get(Disc::COLOR_EMPTY) + 1);
        }
    }

    public function init()
    {
        // 全て空きマス
        for ($x = 1; $x <= self::BOARD_SIZE; $x++) {
            for ($y = 1; $y <= self::BOARD_SIZE; $y++) {
                $this->raw_board[$x][$y] = Disc::COLOR_EMPTY;
            }
        }
        // 壁を作る
        for ($y = 0; $y < self::BOARD_SIZE + 2; $y++) {
            $this->raw_board[0][$y] = Disc::COLOR_WALL;
            $this->raw_board[self::BOARD_SIZE + 1][$y] = Disc::COLOR_WALL;
        }
        for ($x = 0; $x < self::BOARD_SIZE + 2; $x++) {
            $this->raw_board[$x][0] = Disc::COLOR_WALL;
            $this->raw_board[$x][self::BOARD_SIZE + 1] = Disc::COLOR_WALL;
        }

        // 初期配置
        $this->raw_board[3][3] = Disc::COLOR_WHITE;
        $this->raw_board[4][4] = Disc::COLOR_WHITE;
        $this->raw_board[3][4] = Disc::COLOR_BLACK;
        $this->raw_board[4][3] = Disc::COLOR_BLACK;

        $this->discs->set(Disc::COLOR_BLACK, 2);
        $this->discs->set(Disc::COLOR_WHITE, 2);
        $this->discs->set(Disc::COLOR_EMPTY, self::BOARD_SIZE * self::BOARD_SIZE - 4);

        $this->turns = 0;
        $this->current_color = Disc::COLOR_BLACK;

        $this->update_log = [];

        $this->initMovable();
    }
}

$board = new Board();
print_r($board);
