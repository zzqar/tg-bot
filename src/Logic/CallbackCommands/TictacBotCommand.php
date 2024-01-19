<?php

namespace App\Logic\CallbackCommands;

use App\Attribute\Command;
use App\Game\TicTacGame;
use App\Interfaces\CallbackBotCommandInterface;


class TictacBotCommand extends CallbackBotCommandInterface
{

    #[Command('tic_register')]
    public function register(): void
    {
        $user = $this->callback->getFrom();
        $game = new TicTacGame();
        $response = $game->register($user);
        $this->renderResponse($response);
    }

    #[Command('tic_search')]
    public function search(): void
    {
        $response = (new TicTacGame())->search();
        $this->renderResponse($response);
    }

    #[Command('tic_game')]
    public function game(): void
    {
        $user = $this->callback->getFrom();
        $game = new TicTacGame();
        $response = $game->game(
            $user,
            $this->getParamByKey('x'),
            $this->getParamByKey('y'),
            $this->getParamByKey('block'),
        );
        $this->renderResponse($response);
    }

    #[Command('tic_restart')]
    public function restart(): void
    {
        $response = (new TicTacGame())->restart();
        $this->renderResponse($response);
        (new TicTacGame())->setMessId($this->getChatId(), $this->getMessage()->getMessageId());
    }
}
