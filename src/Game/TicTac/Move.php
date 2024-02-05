<?php

namespace App\Game\TicTac;

use App\Game\TicTac\Enum\BoardValue;
use App\Game\TicTac\GameBoard\Board;

class Move
{
    protected int $x;
    protected int $y;
    protected BoardValue $value;

    /**
     * @param BoardValue $value
     * @return Move
     */
    public function setValue(BoardValue $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param int $x
     * @param int $y
     * @return Move
     */
    public function setCoordinate(int $y, int $x): static
    {
        $this->x = $x;
        $this->y = $y;
        return $this;
    }

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * @return BoardValue
     */
    public function getValue(): BoardValue
    {
        return $this->value;
    }

    /**
     * @param Board $board
     * @return Move[]
     */
    public static function emptyCoordinateByBoard(Board $board): array
    {
        $result  = [];
        foreach ($board->getBoard() as  $y => $row) {
            foreach ($row as $x => $cell) {
                if ($cell ===  BoardValue::null) {
                    $result[] = (new self())->setCoordinate($y, $x);
                }
            }
        }
        return $result;
    }
    public static function randomMove($board): Move
    {
        $array = self::emptyCoordinateByBoard($board);
        return $array[array_rand($array)];
    }
}
