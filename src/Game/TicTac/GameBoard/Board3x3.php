<?php

namespace App\Game\TicTac\GameBoard;

use App\Game\TicTac\Enum\BoardValue;

class Board3x3 extends Board
{

    protected function getSize(): int
    {
        return 3;
    }


    public function checkWin(BoardValue $value): bool
    {
        $player = $value;
        // Проверка по горизонтали и вертикали
        for ($i = 0; $i < 3; $i++) {
            if ($this->board[$i][0] === $player
                && $this->board[$i][1] === $player
                && $this->board[$i][2] === $player
            ) {
                return true; // Победа по горизонтали
            }
            if ($this->board[0][$i] === $player
                && $this->board[1][$i] === $player
                && $this->board[2][$i] === $player) {
                return true; // Победа по вертикали
            }
        }

        // Проверка по диагонали (левая верхняя - правая нижняя)
        if ($this->board[0][0] === $player
            && $this->board[1][1] === $player
            && $this->board[2][2] === $player
        ) {
            return true;
        }

        // Проверка по диагонали (правая верхняя - левая нижняя)
        if ($this->board[0][2] === $player
            && $this->board[1][1] === $player
            && $this->board[2][0] === $player
        ) {
            return true;
        }

        return false; // Нет победителя
    }
}
