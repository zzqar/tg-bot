<?php

namespace App\Game\TicTac;

class Score
{
    protected Move $move;
    protected int $score;

    public function __construct(int $score, Move $move = new Move())
    {
        $this->move = $move;
        $this->score = $score;
    }

    /**
     * @return Move
     */
    public function getMove(): Move
    {
        return $this->move;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

}
