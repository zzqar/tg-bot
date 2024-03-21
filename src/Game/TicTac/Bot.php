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
            //$score = $this->miniMax($board, $this->seed, 8);
            // пробуем альфа-бета отсечение
            $score = $this->alphaBeta(
                $board,
                $this->seed,
                8,
                PHP_INT_MIN,
                PHP_INT_MAX
            );
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

        if ($depth === 0 || $board->compileGameLogic() !== MoveResult::RESUME) {
            $bestScore = $this->evaluate($board);

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
                    (!$this->isBotMove($seed) && $currentScore < $bestScore)// ||
                    //($currentScore === $bestScore && random_int(0, 1) === 1)
                ) {
                    $bestScore = $currentScore;
                    $bestPos = $move;
                }

                $move->setValue(BoardValue::null);
                $boardClone->setMove($move);
            }

        }
        return new Score($bestScore,$bestPos);
    }

    private function alphaBeta(Board $board, BoardValue $seed, int $depth, int $alpha, int $beta): Score
    {
        if ($depth === 0 || $board->compileGameLogic() !== MoveResult::RESUME) {
            return new Score($this->evaluate($board));
        }

        $boardClone = clone $board;
        $bestScore = $this->isBotMove($seed) ? PHP_INT_MIN : PHP_INT_MAX;
        $bestPos = new Move();

        foreach (Move::emptyCoordinateByBoard($boardClone) as $move) {
            $move->setValue($seed);
            $boardClone->setMove($move);
            $currentScore = $this->alphaBeta(
                $boardClone,
                $seed->getAnother(),
                $depth - 1,
                $alpha,
                $beta
            )->getScore();

            if (
                ($this->isBotMove($seed) && $currentScore > $bestScore) ||
                (!$this->isBotMove($seed) && $currentScore < $bestScore)// ||
                //($currentScore === $bestScore && random_int(0, 1) === 1)
            ) {
                $bestPos = $move;
            }

            if ($this->isBotMove($seed)) {
                $bestScore = max($bestScore, $currentScore);
                $alpha = max($alpha, $bestScore);
            } else {
                $bestScore = min($bestScore, $currentScore);
                $beta = min($beta, $bestScore);
            }


            if ($beta <= $alpha) {
                break; // Произошло отсечение
            }
            $move->setValue(BoardValue::null);
            $boardClone->setMove($move);
        }
        return new Score($bestScore, $bestPos);
    }


    protected function evaluate(Board $board): int
    {
        $lines = $board->getWinnerLines();
        $sum = 0;
        foreach ($lines as $line) {
            $sum += $board->analyzeLine($line, $this->seed);
        }

        return $sum;
    }

}

