<?php

namespace App\Game\TicTac;

use App\Game\TicTac\Enum\BoardValue;
use App\Game\TicTac\Enum\MoveResult;
use App\Game\TicTac\Enum\State;
use App\Helpers\GameAlert;
use App\Helpers\GameResponse;
use App\Helpers\Response;
use App\Trait\HTML;
use MathieuViossat\Util\ArrayToTextTable;
use TelegramBot\Api\Types\User;

readonly class GameRender
{
    use HTML;

    public function __construct(public TicTacGame $game = new TicTacGame)
    {
    }

    public function render(): Response
    {
        return match ($this->game->getState()) {
            State::menu => $this->renderMenu(),
            State::search => $this->renderSearch(),
            State::pvp, State::bot_play => $this->renderGame(),
            State::end => $this->renderEnd(),
            State::bot_menu => $this->renderBotMenu(),
            State::stats_menu => $this->renderStatsMenu(),
            State::settings => $this->renderSettings(),
            default => new GameAlert("Не определенный статус игры")
        };
    }

    protected function renderMenu(): Response
    {
        $text = [
            $this->textToBoldHTML('Крестики нолики (Меню)'),
            str_repeat('.', 48),
            '- играть 1 на 1',
            '- играть с ботом',
            '- посмотреть статистику',
        ];
        return new GameResponse(
            implode("\n", $text),
            Keyboard::menu()
        );
    }

    protected function renderSearch(): Response
    {
        $players = $this->game->getPlayers();
        $text = [
            $this->textToBoldHTML('Крестики нолики (Поиск)'),
            str_repeat('.', 48),
            'Игроки (' . count($players) . '/2):'
        ];
        foreach ($players as $player) {
            $text[] = $player->getUsername();
        }
        return new GameResponse(
            implode("\n", $text),
            Keyboard::search()
        );
    }

    protected function renderGame(): GameResponse
    {
        $actionItem = BoardValue::getByIndex($this->game->getActionPlayer());
        $players = $this->game->getPlayers();
        /** @var User $player */
        $player = $players[$this->game->getActionPlayer()];
        return new GameResponse(
            implode("\n", [
                "Ход игрока: {$player->getUsername()} -> $actionItem->value",
                $this->renderBoard()
            ]),
            Keyboard::game($this->game->getBoard())
        );
    }

    protected function renderBoard(): string
    {
        $text = [];
        $board = $this->game->getBoard();
        foreach ($board->getBoard() as $line) {
            $text[] = implode('', array_map(
                    static fn($value) => $value->value,
                    $line
                )
            );
        }
        return implode("\n", $text);
    }

    protected function renderEnd(): GameResponse
    {
        $players = $this->game->getPlayers();
        $stats = Statistic::getStats();

        $playerOne = $players[$this->game->getActionPlayer()]->getUsername();
        $playerTwo = $players[(int)!$this->game->getActionPlayer()]->getUsername();

        $moveState = $this->game->getMoveState();
        if ($moveState) {
            $finishText = ($this->game->getMoveState() === MoveResult::WIN)
                ? "Победил: $playerOne"
                : "Ничья";

        }

        $result = [];
        foreach ([$playerOne, $playerTwo] as $userName) {
            $result[$userName] = ['win' => 0, 'loose' => 0];
            foreach ($stats[$userName] ?? [] as $rival) {
                $result[$userName]['win'] += $rival['win'] ?? 0;
                $result[$userName]['loose'] += $rival['loose'] ?? 0;
            }
        }

        $text = [
            $this->textToBoldHTML('Крестики нолики (ФИНИШ)'),
            str_repeat('.', 48),
            $finishText ?? '',
            "$playerOne: {$result[$playerOne]['win']} побед / {$result[$playerOne]['loose']} - проебов",
            "$playerTwo: {$result[$playerTwo]['win']} побед / {$result[$playerTwo]['loose']} - проебов",
            str_repeat('.', 48),
            $this->renderBoard()
        ];

        return new GameResponse(
            implode("\n", $text),
            Keyboard::finish($this->game->checkPreState(State::bot_play))
        );
    }

    protected function renderBotMenu(): Response
    {
        $text = [
            $this->textToBoldHTML('Крестики нолики (BOT)'),
            str_repeat('.', 48),
            'Выберите сложность бота'
        ];
        return new GameResponse(
            implode("\n", $text),
            Keyboard::bot()
        );
    }

    protected function renderStatsMenu(): Response
    {
        $text = [
            $this->textToBoldHTML('Крестики нолики (Статистика)'),
            str_repeat('.', 48),
            '- Посмотреть свою (детальная)',
            '- Посмореть чью-то (детальная)',
            '- Общая',
        ];
        return new GameResponse(
            implode("\n", $text),
            Keyboard::menuStats()
        );
    }

    protected function renderSettings(): GameResponse
    {

        $text = [
            $this->textToBoldHTML('Крестики нолики (Настройки)'),
            str_repeat('.', 48),
            'Параметры'
        ];
        foreach (Statistic::getSettings() as $key => $value) {
            $text[] = ' - ' . $key . ': ' . $value;
        }
        return new GameResponse(
            implode("\n", $text),
            Keyboard::settings()
        );
    }

    public function statsAll(): Response
    {
        $renderData = [];
        foreach (Statistic::getStats() as $name => $rivals) {
            $win = $loose = 0;
            foreach ($rivals as $rival) {
                $win += $rival['win'] ?? 0;
                $loose += $rival['loose'] ?? 0;
            }
            $renderData[] = [
                'Игрок' => $name,
                'Побед' => $win,
                'Проебов' => $loose,
            ];
        }

        $renderer = new ArrayToTextTable($renderData);
        $text = [
            $this->textToBoldHTML('(Статистика)'),
            str_repeat('.', 48),

            $this->textToCodeHTML($renderer->getTable())
        ];
        return new GameResponse(
            implode("\n", $text),
            Keyboard::statsSelf()
        );
    }

    public function renderStatsByName(string $name): GameResponse
    {
        $stats = Statistic::getStats();
        $renderData = [];
        foreach ($stats[$name] ?? [] as $nameRival => $rival) {
            $renderData[] = [
                'Противник' => $nameRival,
                'Побед' => $rival['win'] ?? 0,
                'Проебов' => $rival['loose'] ?? 0,
            ];
        }
        $renderer = new ArrayToTextTable($renderData);
        $result = ($renderData === []) ? "Нет результатов" : $renderer->getTable();
        $text = [
            $this->textToBoldHTML('(Статистика)'),
            str_repeat('.', 48),
            'Игрок: ' . $name,
            $this->textToCodeHTML($result)
        ];
        return new GameResponse(
            implode("\n", $text),
            Keyboard::statsSelf()
        );
    }

    public function statsSelectRender(): Response
    {
        $user = array_keys(Statistic::getStats());

        $text = [
            $this->textToBoldHTML('(Статистика)'),
            str_repeat('.', 48),
            'Выберите игрока',

        ];
        return new GameResponse(
            implode("\n", $text),
            Keyboard::statsSelect($user)
        );
    }

    public function boardSizeSettings(): Response
    {
        $text = [
            $this->textToBoldHTML('(Выбор доски)'),
            str_repeat('.', 48),
        ];
        return new GameResponse(
            implode("\n", $text),
            Keyboard::boardSizeSettings()
        );
    }
}
