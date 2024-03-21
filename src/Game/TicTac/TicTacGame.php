<?php

namespace App\Game\TicTac;

use App\Exceptions\GameException;
use App\Game\TicTac\Enum\BoardValue;
use App\Game\TicTac\Enum\MoveResult;
use App\Game\TicTac\Enum\State;
use App\Game\TicTac\GameBoard\Board;
use App\Game\TicTac\GameBoard\Board3x3;
use App\Game\TicTac\GameBoard\Board4x4;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use TelegramBot\Api\Types\User;

class TicTacGame
{
    protected const CACHE_KEY = 'tic-tack';
    protected FilesystemAdapter $cache;
    protected array $players = [];
    protected int $actionPlayer = 0;
    protected int $botLVL = 0;
    protected array $actionMessId = [];
    protected State $state = State::menu;
    protected State $preState = State::menu;

    protected MoveResult $moveState = MoveResult::RESUME;

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

    public function getState(): State
    {
        return $this->state;
    }

    public function getMessByChatId($chatId)
    {
        return $this->actionMessId[$chatId] ?? null;
    }

    /**
     * @throws GameException
     */
    protected function getOfCache()
    {
        try {
            return $this->cache->get(static::CACHE_KEY, function (ItemInterface $item): array {
                $item->expiresAfter(60 * 60 * 24);
                $board = $this->getBoardBySetting();
                return [
                    'state' => $this->state,
                    'preState' => $this->preState,
                    'board' => $this->board ?? new $board,
                    'action_mess_id' => $this->actionMessId,
                    'players' => $this->players,
                    'action_p' => $this->actionPlayer,
                    'botLVL' => $this->botLVL,
                    'moveState' => $this->moveState
                ];
            });
        } catch (InvalidArgumentException $exception) {
            throw new GameException("Проблемки с получением кеша: " . $exception->getMessage());
        }

    }

    protected function map(array $data): void
    {
        $this->state = $data['state'];
        $this->preState = $data['preState'];
        $this->players = $data['players'];
        $this->actionPlayer = $data['action_p'];
        $this->board = $data['board'];
        $this->actionMessId = $data['action_mess_id'];
        $this->botLVL = $data['botLVL'];
        $this->moveState = $data['moveState'];
    }

    /**
     * @throws GameException
     */
    public static function clearCache(): void
    {
        try {
            (new FilesystemAdapter)->delete(self::CACHE_KEY);
        } catch (InvalidArgumentException $e) {
            throw new GameException("Проблемки с очисткой кеша: " . $e->getMessage());
        }
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
        $this->preState = $this->state;
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
        static::clearCache();
        return $this->getOfCache();
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
        static::clearCache();
        $game = (new static);
        $game->setState($state);
        return $game;
    }

    /**
     * @throws GameException
     */
    public function settingsAction(): void
    {
        $this->setState(State::settings);
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

    /**
     * @throws GameException
     */
    protected function saveResult(): void
    {
        $stats = Statistic::getStats();

        $playerOne = $this->players[$this->actionPlayer]->getUsername();
        $playerTwo = $this->players[(int)!$this->actionPlayer]->getUsername();

        $stats[$playerTwo][$playerOne]['loose'] ??= 0;
        $stats[$playerOne][$playerTwo]['win'] ??= 0;

        $stats[$playerTwo][$playerOne]['loose']++;
        $stats[$playerOne][$playerTwo]['win']++;

        Statistic::setStats($stats);
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

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function getActionPlayer(): int
    {
        return $this->actionPlayer;
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function checkPreState(State $state): bool
    {
        return $this->preState === $state;
    }

    public function getMoveState(): ?MoveResult
    {
        return $this->moveState ?? null;
    }

    protected function getBoardBySetting(): string
    {
        return match(Statistic::getBoard()) {
            4 => Board4x4::class,
            default => Board3x3::class
        };
    }

    public function setBoardSize(int $size)
    {
        Statistic::setBoard($size);
        $this->setState(State::settings);
    }


}
