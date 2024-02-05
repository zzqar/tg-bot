<?php

namespace App\Game\TicTac;

use App\Exceptions\GameException;
use App\Game\TicTac\Enum\BoardValue;
use App\Game\TicTac\Enum\MoveResult;
use App\Game\TicTac\Enum\State;
use App\Game\TicTac\GameBoard\Board;
use App\Game\TicTac\GameBoard\Board3x3;
use App\Helpers\GameAlert;
use App\Helpers\GameResponse;
use App\Helpers\Response;
use App\Trait\HTML;
use Exception;
use MathieuViossat\Util\ArrayToTextTable;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use TelegramBot\Api\Types\User;
use Throwable;

class TicTacGame
{
    use HTML;

    protected const STATS_FILENAME = 'tictac_stats.json';
    protected string $cacheKey = 'tic-tack';
    protected FilesystemAdapter $cache;
    protected array $players = [];
    protected int $actionPlayer = 0;
    protected int $botLVL = 0;
    protected array $actionMessId = [];
    protected State $state = State::menu;
    protected ?MoveResult $moveState = null;

    protected Board $board;

    /**
     * @throws GameException
     */
    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
        $data = $this->getOfCache();
        $this->map($data);
    }

    public function getMessByChatId($chatId)
    {
        return $this->actionMessId[$chatId] ?? null;
    }

    protected function getStats(): array
    {
        try {
            return json_decode(file_get_contents(self::STATS_FILENAME), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @throws GameException
     */
    private function setStats(array $stats): void
    {
        try {
            file_put_contents(self::STATS_FILENAME, json_encode($stats, JSON_THROW_ON_ERROR));
        } catch (Throwable $e) {
            throw new GameException($e->getMessage());
        }

    }

    /**
     * @throws GameException
     */
    protected function getOfCache()
    {
        try {
            return $this->cache->get($this->cacheKey, function (ItemInterface $item): array {
                $item->expiresAfter(60 * 60 * 24);
                return [
                    'state' => $this->state,
                    'board' => $this->board ?? new Board3x3(),
                    'action_mess_id' => $this->actionMessId,
                    'players' => $this->players,
                    'action_p' => $this->actionPlayer,
                    'botLVL' => $this->botLVL,
                ];
            });
        } catch (InvalidArgumentException $exception) {
            throw new GameException("Проблемки с получением кеша: " . $exception->getMessage());
        }

    }

    protected function map(array $data): void
    {
        $this->state = $data['state'];
        $this->players = $data['players'];
        $this->actionPlayer = $data['action_p'];
        $this->board = $data['board'];
        $this->actionMessId = $data['action_mess_id'];
        $this->botLVL = $data['botLVL'];
    }

    /**
     * @throws GameException
     */
    protected function clearCache(): void
    {
        try {
            $this->cache->delete($this->cacheKey);
        } catch (InvalidArgumentException $e) {
            throw new GameException("Проблемки с очисткой кеша: " . $e->getMessage());
        }
    }

    public function renderByState(): Response
    {
        return match ($this->state) {
            State::menu => $this->renderMenu(),
            State::search => $this->renderSearch(),
            State::pvp, State::bot_play => $this->renderGame(),
            State::end => $this->renderEnd(),
            State::bot_menu => $this->renderBotMenu(),
            State::stats_menu => $this->renderStatsMenu(),
            default => new GameAlert("Не определенный статус игры")
        };
    }

    /**
     * @throws GameException
     */
    protected function addPlayer(User $user): bool
    {
        if (
            count($this->players) > 1
            || in_array(
                $user->getId(),
                $this->getPlayerIds(),
                true
            )
        ) {
            return false;
        }
        $this->players[] = $user;
        $this->resetCache();
        return true;
    }

    protected function getPlayerIds(): array
    {
        $ids = [];
        /** @var User $player */
        foreach ($this->players as $player) {
            $ids[] = $player->getId();
        }
        return $ids;
    }

    /**
     * @throws GameException
     */
    public function setMessId($chatID, float|int $getMessageId): void
    {
        $this->actionMessId[$chatID] = $getMessageId;
        $this->resetCache();
    }

    /**
     * @throws GameException
     */
    protected function setState(State $state): void
    {
        $this->state = $state;
        $this->resetCache();
    }

    /**
     * @throws GameException
     */
    protected function setBotLVL(int $lvl): void
    {
        $this->botLVL = $lvl;
        $this->resetCache();
    }


    /**
     * @throws GameException
     */
    public function resetCache(): mixed
    {
        $this->clearCache();
        return $this->getOfCache();
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
        $count = count($this->players);
        $text = [
            $this->textToBoldHTML('Крестики нолики (Поиск)'),
            str_repeat('.', 48),
            'Игроки (' . $count . '/2):'
        ];
        foreach ($this->players as $player) {
            $text[] = $player->getUsername();
        }
        return new GameResponse(
            implode("\n", $text),
            Keyboard::search()
        );
    }

    protected function renderGame(): GameResponse
    {
        $actionItem = BoardValue::getByIndex($this->actionPlayer);
        /** @var User $player */
        $player = $this->players[$this->actionPlayer];
        return new GameResponse(
            implode("\n", [
                "Ход игрока: {$player->getUsername()} -> $actionItem->value",
                $this->board->renderBoard()
            ]),
            Keyboard::game($this->board)
        );
    }

    /**
     * @throws GameException
     */
    public function registerAction(User $user): void
    {
        if (!$this->addPlayer($user)) {
            throw new GameException('Ты уже учавствуешь');
        }
        if (count($this->players) === 2) {
            $this->setState(State::pvp);
        }
    }

    /**
     * @throws GameException
     */
    public function restartAction(State $state, bool $force = false): static
    {
        if ($this->state === $state && $this->players === [] && !$force) {
            throw new GameException('Кеш уже чист');
        }
        $this->clearCache();
        $game = (new static);
        $game->setState($state);
        return $game;
    }

    /**
     * @throws GameException
     */
    public function moveAction(User $user, Move $move): void
    {
        $playerIds = $this->getPlayerIds();
        if (!in_array($user->getId(), $this->getPlayerIds(), true)) {
            throw new GameException('ТЫ не участвуешь в игре! Пшел от сюда!');
        }
        if ($user->getId() !== $playerIds[$this->actionPlayer]) {
            throw new GameException('Подожди своего хода');
        }
        $move->setValue(BoardValue::getByIndex($this->actionPlayer));
        $this->move($move);

        /** @var User $playerTwo */
        $playerTwo = $this->players[$this->actionPlayer];

        if ($playerTwo->isBot()) {
            $this->botAction();
        }
    }

    /**
     * @throws GameException
     */
    protected function move(Move $move): void
    {
        $this->moveState = $this->board->makeMove($move);
        $this->resetCache();

        switch ($this->moveState) {
            case MoveResult::RESUME:
                $this->actionPlayer = (int)!$this->actionPlayer;
                $this->resetCache();
                break;
            case MoveResult::WIN:
                $this->saveResult();
                $this->setState(State::end);
                break;
            case MoveResult::DRAW:
                $this->setState(State::end);
                break;
        }
    }

    /**
     * @throws GameException
     * @throws Exception
     */
    public function botAction(): void
    {
        $bot = new Bot($this->botLVL);
        $bot->setSeed(BoardValue::getByIndex($this->actionPlayer));
        $botMove = $bot->howMove($this->board);
        $this->move($botMove);
    }


    /**
     * @throws GameException
     */
    public function botMenuAction(User $user): static
    {
        $static = $this->restartAction(State::bot_menu);
        $static->addPlayer($user);
        return $static;
    }

    public function statsSelfAction(User $user): Response
    {
        return $this->renderStatsByName($user->getUsername());
    }

    protected function renderStatsByName(string $name): GameResponse
    {
        $stats = $this->getStats();
        $renderData = [];
        foreach ($stats[$name] ?? [] as $nameRival => $rival){
            $renderData[] = [
                'Противник' => $nameRival,
                'Побед' => $rival['win'] ?? 0,
                'Проебов' => $rival['loose'] ?? 0,
            ];
        }
        $renderer = new ArrayToTextTable($renderData);
        $result = ($renderData === []) ? "Нет результатов" :  $renderer->getTable();
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

    public function statsTargetAction(string $user): Response
    {
        return $this->renderStatsByName($user);
    }
    public function statsAll(): Response
    {
        $stats = $this->getStats();

        $renderData = [];
        foreach ($stats ?? [] as $name => $rivals){
            $win = $loose = 0;
            foreach($rivals as $rival){
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

    public function statsSelectAction(): Response
    {
        $stats = $this->getStats();
        $user = array_keys($stats);

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


    /**
     * @throws GameException
     */
    protected function saveResult(): void
    {
        $stats = $this->getStats();

        $playerOne = $this->players[$this->actionPlayer]->getUsername();
        $playerTwo = $this->players[(int)!$this->actionPlayer]->getUsername();

        $stats[$playerTwo][$playerOne]['loose'] ??= 0;
        $stats[$playerOne][$playerTwo]['win'] ??= 0;

        $stats[$playerTwo][$playerOne]['loose']++;
        $stats[$playerOne][$playerTwo]['win']++;

        $this->setStats($stats);
    }

    protected function renderEnd(): GameResponse
    {
        $stats = $this->getStats();

        $playerOne = $this->players[$this->actionPlayer]->getUsername();
        $playerTwo = $this->players[(int)!$this->actionPlayer]->getUsername();

        $finishText = (isset($this->moveState) && $this->moveState === MoveResult::WIN) ? "Победил: $playerOne" : "Ничья";
        $p1 = [];
        $p1['win']  = 0;
        $p1['loose'] = 0;
        $p2 = $p1;
        foreach ($stats[$playerOne] ?? [] as $rival){
            $p1['win'] += $rival['win'] ?? 0;
            $p1['loose'] += $rival['loose'] ?? 0;
        }
        foreach ($stats[$playerTwo] ?? [] as $rival){
            $p2['win'] += $rival['win'] ?? 0;
            $p2['loose'] += $rival['loose'] ?? 0;
        }

        $text = [
            $this->textToBoldHTML('Крестики нолики (ФИНИШ)'),
            str_repeat('.', 48),
            $finishText,
            "$playerOne: {$p1['win']} побед / {$p1['loose']} - проебов",
            "$playerTwo: {$p2['win']} побед / {$p2['loose']} - проебов",
            str_repeat('.', 48),
            $this->board->renderBoard()
        ];

        return new GameResponse(
            implode("\n", $text),
            Keyboard::finish()
        );
    }

    public function renderBotMenu(): Response
    {
        $text = [
            $this->textToBoldHTML('Крестики нолики (BOT)'),
            'Выберите сложность бота'
        ];
        return new GameResponse(
            implode("\n", $text),
            Keyboard::bot()
        );
    }

    /**
     * @throws GameException
     */
    public function startBotAction(int $lvl, User $bot): void
    {
        $this->setBotLVL($lvl);
        $bot->setUsername("BOT-" . $lvl);
        $this->addPlayer($bot);
        $this->setState(State::bot_play);
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

    /**
     * @throws GameException
     */
    public function clearStats(): void
    {
        $this->setStats([]);
    }


}
