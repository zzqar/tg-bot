<?php

namespace App\Game\TicTac\GameBoard;

use App\Exceptions\GameException;
use App\Game\TicTac\Enum\BoardValue;
use App\Game\TicTac\Enum\MoveResult;
use App\Game\TicTac\Move;

abstract class Board
{
    protected array $board;

    public function __construct()
    {
        $size = $this->getSize();
        $this->board = $this->createEmptyBoard($size);
    }

    protected function createEmptyBoard(int $size): array
    {
        $board = [];
        for ($i = 0; $i < $size; $i++) {
            $board[] = array_fill(0, $size, BoardValue::null);
        }
        return $board;
    }

    abstract protected function getSize(): int;

    /**
     * @throws GameException
     */
    public function makeMove(Move $move): MoveResult
    {
        if (!$this->canMove($move)) {
            throw new GameException('Клета занята или не существует');
        }
        $this->setMove($move);
        return $this->compileGameLogic($move->getValue());

    }

    public function compileGameLogic(BoardValue $value): MoveResult
    {
        if ($this->checkWin($value)) {
            return MoveResult::WIN;
        }
        if ($this->checkDraw()) {
            return MoveResult::DRAW;
        }
        return MoveResult::RESUME;
    }

    public function setMove(Move $move): void
    {
        $this->board[$move->getY()][$move->getX()] = $move->getValue();
    }

    protected function canMove(Move $move): bool
    {
        return isset($this->board[$move->getY()][$move->getX()])
            && $this->board[$move->getY()][$move->getX()] === BoardValue::null;
    }

    abstract public function checkWin(BoardValue $value): bool;

    protected function checkDraw(): bool
    {
        // Проверяем, есть ли ещё пустые клетки на доске
        foreach ($this->board as $row) {
            foreach ($row as $cell) {
                if ($cell ===  BoardValue::null) {
                    return false; // На доске есть пустая клетка, игра продолжается
                }
            }
        }
        return true;
    }

    public function renderBoard(): string
    {
        $text = [];
        foreach ($this->board as $line) {
            $text[] = implode('', array_map(
                    static fn($value) => $value->value,
                    $line
                )
            );
        }
        return implode("\n", $text);
    }

    /**
     * @return array
     */
    public function getBoard(): array
    {
        return $this->board;
    }


}
