<?php

namespace App\Game\TicTac;

use App\Game\TicTac\Enum\BoardValue;
use App\Game\TicTac\Enum\MoveResult;
use App\Game\TicTac\GameBoard\Board;
use Exception;

class Bot
{
    protected BoardValue $seed;
    protected BoardValue $seedOpp;

    public function __construct(readonly int $lvl)
    {
    }

    /**
     * @throws Exception
     */
    public function howMove(Board $board): Move
    {
        $random = random_int(0, 100);
        $x = 100 - $this->lvl;
        if ($random < $x) {
            $move = Move::randomMove($board);
        } else {
            $score = $this->miniMax($board, $this->seed, 9);
            $move = $score->getMove();
        }
        $move->setValue($this->seed);
        return $move;
    }

    public function setSeed(BoardValue $seed): void
    {
        $this->seed = $seed;
        $this->seedOpp = $seed->getAnother();
    }

    protected function isBotMove(BoardValue $seed): bool
    {
        return $seed === $this->seed;
    }

    /**
     * @throws Exception
     */
    private function miniMax(Board $board, BoardValue $seed, int $depth): Score
    {

        $bestScore = $this->isBotMove($seed) ? PHP_INT_MIN : PHP_INT_MAX;
        $bestPos = new Move();
        if ($board->compileGameLogic($seed->getAnother()) !== MoveResult::RESUME) {
            $bestScore = $this->simpleEvaluate($board);

        } else {
            $boardClone = clone $board;

            foreach (Move::emptyCoordinateByBoard($boardClone) as $move) {
                $move->setValue($seed);
                $boardClone->setMove($move);
                $currentScore = $this
                    ->miniMax(
                        $boardClone,
                        $seed->getAnother(),
                        $depth - 1
                    )->getScore();
                if (
                    ($this->isBotMove($seed) && $currentScore > $bestScore) ||
                    (!$this->isBotMove($seed) && $currentScore < $bestScore) ||
                    ($currentScore === $bestScore && random_int(0, 1) === 1)
                ) {
                    $bestScore = $currentScore;
                    $bestPos = $move;
                }

                $move->setValue(BoardValue::null);
                $boardClone->setMove($move);
            }

        }
        return new Score($bestPos, $bestScore);
    }

    /**
     * Анализ доски - подсчет очков
     *
     * @param Board $board
     * @return int
     */
    private function simpleEvaluate(Board $board): int
    {
        if ($board->checkWin($this->seed)) {
            return 10;
        }
        if ($board->checkWin($this->seedOpp)) {
            return -10;
        }
        return 0;
    }

}

