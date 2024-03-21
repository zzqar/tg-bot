<?php

namespace App\Game\TicTac\GameBoard;

use App\Exceptions\GameException;
use App\Game\TicTac\Enum\BoardValue;
use App\Game\TicTac\Enum\MoveResult;
use App\Game\TicTac\Move;

abstract class Board
{
    protected array $board;
    protected Move $lastMove;

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
        return $this->compileGameLogic();

    }

    public function compileGameLogic(): MoveResult
    {
        if ($this->checkWin()) {
            return MoveResult::WIN;
        }
        if ($this->checkDraw()) {
            return MoveResult::DRAW;
        }
        return MoveResult::RESUME;
    }

    public function setMove(Move $move): void
    {
        $this->lastMove = $move;
        $this->board[$move->getY()][$move->getX()] = $move->getValue();
    }
    protected function getByCoordinate(array $coordinate): BoardValue
    {
        return  $this->board[$coordinate[0]][$coordinate[1]];
    }


    protected function canMove(Move $move): bool
    {
        return isset($this->board[$move->getY()][$move->getX()])
            && $this->board[$move->getY()][$move->getX()] === BoardValue::null;
    }

    public function checkWin(): bool
    {
        $checkCoordinate = [$this->lastMove->getY(), $this->lastMove->getX()];
        foreach ($this->getWinnerCoordinates() as $line) {
            if (in_array($checkCoordinate, $line, true))
            {
                $results = $this->analyzeLine(
                    $this->getValuesByLineCoordinate($line),
                    $this->lastMove->getValue()
                );

                if ($results === 100000) {
                    return true;
                }
            }
        }
        return false;
    }


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

    /**
     * @return array
     */
    public function getBoard(): array
    {
        return $this->board;
    }

    public function getWinnerLines(): array
    {
        $coordinateArray = $this->getWinnerCoordinates();
        $result = [];
        foreach ($coordinateArray as $lineCoordinate) {
            $result[] = $this->getValuesByLineCoordinate($lineCoordinate);
        }
        return $result;
    }

    protected  function getValuesByLineCoordinate($lineCoordinate): array
    {
        $line = [];
        foreach ($lineCoordinate as $itemCoordinate) {
            $line[] = $this->getByCoordinate($itemCoordinate);
        }
        return $line;

    }

    abstract protected function getWinnerCoordinates(): array;

    public function analyzeLine(array $arr, BoardValue $value): int
    {
        $count = [];
        $countElem = 0;
        foreach ($arr as $element) {
            if (!isset($count[$element->name])) {
                $count[$element->name] = 0;
            }
            $count[$element->name]++;
            $countElem++;
        }

        $countEnemy = $count[$value->getAnother()->name] ?? 0;
        $countPlayer = $count[$value->name] ?? 0;
        $countNull = $count[BoardValue::null->name] ?? 0;

        /**
         * Правило 1: 100% - ничья
         */
        if ($countEnemy > 0 && $countPlayer > 0) {
            return 0;
        }
        // пустые клетки => лучше, чем ничья
        if ($countNull === $countElem) {
            return 10;
        }

        /**
         * Правило 2: 100% - победа
         */
        if ($countPlayer === $countElem) {
            return 100000;
        }

        /**
         * Правило 3: 100% - поражение
         */
        if ($countEnemy === $countElem) {
            return -100;
        }

        /**
         * Правило 4: Потенциальное поражение
         */
        if ($countEnemy > 0 && $countPlayer === 0) {
            return -50;
        }

        /**
         * Правило 5: Потенциальная победа
         */
        if ($countPlayer > 0 && $countEnemy === 0) {
            return 50;
        }
        return 1; // Значение по умолчанию
    }

}
