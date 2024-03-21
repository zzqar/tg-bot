<?php

namespace App\Logic\CallbackCommands;

use App\Attribute\Command;
use App\Exceptions\GameException;
use App\Game\TicTac\Enum\State;
use App\Game\TicTac\GameRender;
use App\Game\TicTac\Move;
use App\Game\TicTac\TicTacGame;
use App\Helpers\GameAlert;
use App\Interfaces\CallbackBotCommandInterface;


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
        try {
            (new TicTacGame)
                ->restartAction(State::search)
                ->setMessId($this->getChatId(), $this->getMessageId());

            $this->renderResponse((new GameRender)->render());
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
        }

    }

    #[Command('tic_menu')]
    public function menu(): void
    {
        try {
            (new TicTacGame)
                ->restartAction(State::menu)
                ->setMessId($this->getChatId(), $this->getMessageId());

            $this->renderResponse((new GameRender)->render());

        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
            return;
        }
    }

    #[Command('tic_register')]
    public function register(): void
    {
        try {
            (new TicTacGame)->registerAction($this->callback->getFrom());

            $this->renderResponse((new GameRender)->render());

        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
        }
    }


    #[Command('tic_move', true)]
    public function move(): void
    {
        if ($this->getParamByKey('block')) {
            $this->renderResponse(new GameAlert('Кнопка залочена. Че жмешь?'));
            return;
        }
        try {
            (new TicTacGame)->moveAction(
                $this->callback->getFrom(),
                (new Move)->setCoordinate(
                    $this->getParamByKey('y'),
                    $this->getParamByKey('x')
                )
            );
            $this->renderResponse((new GameRender)->render());
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
            return;
        }
    }

    #[Command('tic_bot_menu')]
    public function botMenu(): void
    {
        try {
            (new TicTacGame)
                ->botMenuAction($this->callback->getFrom())
                ->setMessId($this->getChatId(), $this->getMessageId());

            $this->renderResponse( (new GameRender)->render());
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
        }
    }

    #[Command('tic_bot_start', true)]
    public function startWithBot(): void
    {
        try {
            (new TicTacGame)->startBotAction(
                $this->getParamByKey('lvl'),
                $this->getMessage()->getFrom()
            );

            $this->renderResponse( (new GameRender)->render());
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
        }
    }

    #[Command('tic_stats', true)]
    public function statsMenu(): void
    {
        try {
            (new TicTacGame)
                ->restartAction(State::stats_menu, true)
                ->setMessId($this->getChatId(), $this->getMessageId());

            $this->renderResponse((new GameRender)->render());
        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
        }
    }

    #[Command('tic_stats_self', true)]
    public function statsSelf(): void
    {
        $this->renderResponse(
            (new GameRender)->renderStatsByName(
                $this->callback->getFrom()->getUsername()
            )
        );
    }

    #[Command('tic_setting', true)]
    public function setting(): void
    {
        try {
            (new TicTacGame)->settingsAction();

            $this->renderResponse((new GameRender())->render());

        } catch (GameException $e) {
            $this->renderResponse(new GameAlert($e->getMessage()));
            return;
        }
    }

    #[Command('tic_stats_select', true)]
    public function statsSelect(): void
    {
        $this->renderResponse(
            (new GameRender)->statsSelectRender()
        );
    }

    #[Command('tic_stats_target', true)]
    public function statsTarget(): void
    {
        $this->renderResponse(
            (new GameRender)->renderStatsByName(
                $this->getParamByKey('name')
            )
        );
    }

    #[Command('tic_stats_all', true)]
    public function statsAll(): void
    {
        $this->renderResponse( (new GameRender)->statsAll());
    }

    #[Command('tic_setting_board_size_select', true)]
    public function boardSize(): void
    {
        $this->renderResponse( (new GameRender)->boardSizeSettings());
    }

    #[Command('tic_set_board_size', true)]
    public function setBoardSize(): void
    {
        (new TicTacGame)->setBoardSize($this->getParamByKey('size'));
        $this->renderResponse( (new GameRender)->render());
    }




}
