<?php

namespace App\Logic\CallbackCommands;

use App\Attribute\Command;
use App\Exceptions\GameException;
use App\Game\TicTac\Enum\State;
use App\Game\TicTac\Move;
use App\Game\TicTac\TicTacGame;
use App\Helpers\GameAlert;
use App\Interfaces\CallbackBotCommandInterface;
use TelegramBot\Api\Types\User;


class TictacBotCommand extends CallbackBotCommandInterface
{
    protected string $parseMode = 'html';

    #[Command('tic_zag')]
    public function zaglushka(): void
    {
        $this->renderResponse(new GameAlert('Не реализованно! ЖДИ!'));
    }

    #[Command('tic_search')]
    public function search(): void
    {
        $game = new TicTacGame;
        try {
            $static = $game->restartAction(State::search);
            $static->setMessId($this->getChatId(), $this->getMessageId());
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
            return;
        }
        $this->renderResponse($static->renderByState());
    }

    #[Command('tic_menu')]
    public function menu(): void
    {
        $game = new TicTacGame;
        try {
            $static = $game->restartAction(State::menu);
            $static->setMessId($this->getChatId(), $this->getMessageId());
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
            return;
        }
        $this->renderResponse($static->renderByState());
    }

    #[Command('tic_register')]
    public function register(): void
    {
        $user = $this->callback->getFrom();
        $game = new TicTacGame;
        try {
            $game->registerAction($user);
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
            return;
        }
        $this->renderResponse($game->renderByState());
    }


    #[Command('tic_move', true)]
    public function move(): void
    {
        if ($this->getParamByKey('block')) {
            $this->renderResponse(new GameAlert('Кнопка залочена. Че жмешь?'));
            return;
        }

        $game = new TicTacGame;
        $user = $this->callback->getFrom();
        $move = (new Move)->setCoordinate(
            $this->getParamByKey('y'),
            $this->getParamByKey('x')
        );
        try {
            $game->moveAction($user, $move);
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
            return;
        }
        $this->renderResponse($game->renderByState());
    }

    #[Command('tic_bot_menu')]
    public function botMenu(): void
    {
        $game = new TicTacGame;
        $user = $this->callback->getFrom();
        try {
            $static = $game->botMenuAction($user);
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
            return;
        }
        $this->renderResponse($static->renderByState());
    }

    #[Command('tic_bot_start', true)]
    public function startWithBot(): void
    {
        /** @var User $bot */
        $bot = $this->getMessage()->getFrom();
        $game = new TicTacGame;
        $lvl = $this->getParamByKey('lvl', 0);
        try {
            $game->startBotAction($lvl, $bot);
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
            return;
        }
        $this->renderResponse($game->renderByState());
    }

    #[Command('tic_stats', true)]
    public function statsMenu(): void
    {
        $game = new TicTacGame;
        try {
            $static = $game->restartAction(State::stats_menu, true);
            $static->setMessId($this->getChatId(), $this->getMessageId());
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
            return;
        }
        $this->renderResponse($static->renderByState());

    }

    #[Command('tic_stats_self', true)]
    public function statsSelf(): void
    {
        $game = new TicTacGame;
        $this->renderResponse(
            $game->statsSelfAction($this->callback->getFrom())
        );

    }

    #[Command('tic_stats_select', true)]
    public function statsSelect(): void
    {
        $game = new TicTacGame;
        $this->renderResponse(
            $game->statsSelectAction()
        );
    }

    #[Command('tic_stats_target', true)]
    public function statsTarget(): void
    {
        $game = new TicTacGame;
        $name = $this->getParamByKey('name');
        $this->renderResponse(
            $game->statsTargetAction($name)
        );
    }

    #[Command('tic_stats_all', true)]
    public function statsAll(): void
    {
        $game = new TicTacGame;
        $this->renderResponse(
            $game->statsAll()
        );
    }


}
