<?php

namespace App\Game;

use App\Exceptions\GameException;
use App\Helpers\GameAlert;
use App\Helpers\GameResponse;
use App\Helpers\Response;
use JsonException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\User;
use Throwable;

class TicTacGame
{
    public const SEARCHING = 1;
    public const PLAYING = 2;
    protected const STATS_FILENAME = 'tictac_stats.json';
    protected const GAME = 0;
    protected const WIN = 1;
    protected const DRAW = 2;

    protected int $gameState = self::SEARCHING;
    protected array $players = [];
    protected int $actionPlayer = 0;
    protected array $bord = [
        ['-', '-', '-'],
        ['-', '-', '-'],
        ['-', '-', '-'],
    ];
    protected const PLAYER_ITEM = [0 => 'x', 1 => 'о'];
    protected string $cacheKey = 'tic-tack';
    protected FilesystemAdapter $cache;
    protected array $actionMessId = [];

    /**
     * @throws GameException
     */
    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
        $data = $this->getOfCache();
        $this->map($data);
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

    protected function getPlayerIds(): array
    {
        $ids = [];
        /** @var User $player */
        foreach ($this->players as $player) {
            $ids[] = $player->getId();
        }
        return $ids;
    }

    protected function map(array $data): void
    {
        $this->players = $data['players'];
        $this->gameState = $data['state'];
        $this->actionPlayer = $data['action_p'];
        $this->bord = $data['bord'];
        $this->actionMessId = $data['action_mess_id'];
    }

    /**
     * @throws GameException
     */
    protected function addPlayer(User $user): bool
    {
        if (count($this->players) > 1 || in_array($user->getId(), $this->getPlayerIds(), true)) {
            return false;
        }
        $this->players[] = $user;
        $this->resetCache();
        return true;
    }

    /**
     * @throws GameException
     */
    public function setMessId($chatID, float|int $getMessageId): void
    {
        $this->actionMessId[$chatID] = $getMessageId;
        $this->resetCache();
    }

    protected function setState(int $state): void
    {
        $this->gameState = $state;
    }

    /**
     * @throws GameException
     */
    public function resetCache(): mixed
    {
        $this->clearCache();
        return $this->getOfCache();
    }

    public function restart(): Response
    {
        try {
            if ($this->gameState === self::SEARCHING && $this->players === []) {
                return new GameAlert('Кеш уже чист');
            }

            $this->clearCache();
            return (new self())->search();
        } catch (GameException $e) {
            return new GameResponse($e->getMessage(), null);
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
                    'action_mess_id' => $this->actionMessId,
                    'players' => $this->players,
                    'state' => $this->gameState,
                    'action_p' => $this->actionPlayer,
                    'bord' => $this->bord
                ];
            });
        } catch (InvalidArgumentException $exception) {
            throw new GameException("Проблемки с получением кеша: " . $exception->getMessage());
        }

    }

    public function search(): Response
    {
        if ($this->gameState !== self::SEARCHING) {
            return $this->renderGame();
        }
        $count = count($this->players);
        $text[] = '*Крестики нолики*';
        $text[] = 'Игроки (' . $count . '/2):';
        if ($count > 0) {
            /** @var User $usr */
            foreach ($this->players as $usr) {
                $text[] = $usr->getUsername();
            }
        }
        $text[] = $this->renderUUID();
        return new GameResponse(implode("\n", $text), $this->searchKeyboard());
    }

    protected function searchKeyboard(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => 'Учавствовать', 'callback_data' => '/tic_register']
            ],
            [
                ['text' => 'Перезапустить', 'callback_data' => '/tic_restart']
            ],
        ]);

    }

    protected function finishKeyboard(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => 'Новая игра', 'callback_data' => '/tic_search']
            ],
        ]);

    }

    protected function gameKeyboard(): InlineKeyboardMarkup
    {
        $key = [];
        foreach ($this->bord as $y => $line) {
            $lineKey = [];
            foreach ($line as $x => $item) {
                if ($item === '-') {
                    $lineKey[] = ['text' => "🟩", 'callback_data' => "/tic_game -x=$x -y=$y"];
                } else {
                    $lineKey[] = ['text' => "🔒", 'callback_data' => "/tic_game -block=1 "];
                }
            }
            $key[] = $lineKey;
        }
        $key[] = [['text' => 'Перезапустить', 'callback_data' => '/tic_restart']];
        return new InlineKeyboardMarkup($key);
    }

    protected function renderBoard(): string
    {
        $text = [];
        $text[] = str_repeat('.', 40);
        foreach ($this->bord as $line) {
            $text[] = implode('', $line);
        }
        $text = implode("\n", $text);
        return str_replace(['-', 'x', 'о'], ["◻️", "❌", "⭕"], $text);
    }
    protected function renderUUID(): string
    {
        $text[] = str_repeat('.', 40);
        $text[] = 'uuid: ' . time();
        return implode("\n", $text);
    }
    protected function renderGame(): GameResponse
    {
        $actionItem = self::PLAYER_ITEM[$this->actionPlayer];
        /** @var User $player */
        $player = $this->players[$this->actionPlayer];

        $text[] = "Ход игрока: {$player->getUsername()} -> $actionItem";

        $text[] = $this->renderBoard();
        $text[] = $this->renderUUID();

        return new GameResponse(implode("\n", $text), $this->gameKeyboard());
    }
    protected function renderFinish($result): GameResponse
    {
        $text[] = $result;
        $text[] = $this->renderBoard();
        $text[] = $this->renderUUID();

        return new GameResponse(implode("\n", $text), $this->finishKeyboard());
    }

    /**
     * @throws GameException|JsonException
     */
    public function game(User $user, ?string $x = null, ?string $y =  null, ?string $block = null): Response
    {
        if ($this->gameState !== self::PLAYING) {
            return $this->search();
        }
        $playerIds = $this->getPlayerIds();
        if ($x && !in_array($user->getId(), $this->getPlayerIds(), true)) {
            return new GameAlert('ТЫ не участвуешь в игре! Пшел от сюда!');
        }
        if ($block) {
            return new GameAlert('Кнопка залочена. Че жмешь?');
        }
        if ($x && $user->getId() !== $playerIds[$this->actionPlayer]) {
            return new GameAlert('Подожди своего хода');
        }
        $item = self::PLAYER_ITEM[$this->actionPlayer];
        $this->bord[$y][$x] = $item;

        $gameState = $this->compileGameLogick();
        $playerOne = $this->players[$this->actionPlayer];
        $playerTwo = $this->players[(int)!$this->actionPlayer];

        switch ($gameState){
            case self::WIN:
                $text[] = 'Победил игрок: ' . $user->getUsername();
                $text[] = $this->saveResult($playerOne->getUsername(), $playerTwo->getUsername(), true);
                $this->clearCache();
                return $this->renderFinish(implode("\n", $text));

            case self::DRAW:
                $text[] = 'Ничья';
                $text[] = $this->saveResult($playerOne->getUsername(), $playerTwo->getUsername());
                $this->clearCache();
                return $this->renderFinish(implode("\n", $text));

            default:
                $this->actionPlayer = (int)!$this->actionPlayer;
                $this->resetCache();
                return $this->renderGame();
        }
    }

    public function register(User $user): Response
    {
        try {
            if ($this->addPlayer($user)) {
                if (count($this->players) === 2) {
                    $this->setState(self::PLAYING);
                    $this->resetCache();
                    return $this->renderGame();

                }
                return $this->search();
            }
            return new GameAlert('Ты уже учавствуешь');

        } catch (GameException $e) {
            return new GameResponse('Что то пошло не так...:' . $e->getMessage(), null);
        }
    }

    public function getMessByChatId($chatId)
    {
        return $this->actionMessId[$chatId] ?? null;
    }

    protected function checkWin(): bool
    {
        $player = self::PLAYER_ITEM[$this->actionPlayer];
        // Проверка по горизонтали и вертикали
        for ($i = 0; $i < 3; $i++) {
            if ($this->bord[$i][0] === $player
                && $this->bord[$i][1] === $player
                && $this->bord[$i][2] === $player
            ) {
                return true; // Победа по горизонтали
            }
            if ($this->bord[0][$i] === $player
                && $this->bord[1][$i] === $player
                && $this->bord[2][$i] === $player) {
                return true; // Победа по вертикали
            }
        }

        // Проверка по диагонали (левая верхняя - правая нижняя)
        if ($this->bord[0][0] === $player
            && $this->bord[1][1] === $player
            && $this->bord[2][2] === $player
        ) {
            return true;
        }

        // Проверка по диагонали (правая верхняя - левая нижняя)
        if ($this->bord[0][2] === $player
            && $this->bord[1][1] === $player
            && $this->bord[2][0] === $player
        ) {
            return true;
        }

        return false; // Нет победителя
    }

    protected function checkDraw(): bool
    {
        // Проверяем, есть ли ещё пустые клетки на доске
        foreach ($this->bord as $row) {
            foreach ($row as $cell) {
                if ($cell === '-') {
                    return false; // На доске есть пустая клетка, игра продолжается
                }
            }
        }
        return true;
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
     * @throws JsonException
     */
    protected function saveResult($nameWin, $nameLoose, $win = false): string
    {
        $stats = $this->getStats();
        if (empty($stats[$nameWin])) {
            $stats[$nameWin] = ['win' => 0, 'loose' => 0];
        }
        if (empty($stats[$nameLoose])) {
            $stats[$nameLoose] = ['win' => 0, 'loose' => 0];
        }
        if ($win){
            $stats[$nameLoose]['loose']++;
            $stats[$nameWin]['win']++;
            file_put_contents(self::STATS_FILENAME, json_encode($stats, JSON_THROW_ON_ERROR));
        }
        $text[] = "$nameWin: {$stats[$nameWin]['win']} побед / {$stats[$nameWin]['loose']} - проебов";
        $text[] = "$nameLoose: {$stats[$nameLoose]['win']} побед / {$stats[$nameLoose]['loose']} - проебов";

        return implode("\n", $text);
    }

    protected function compileGameLogick(): int
    {
        if ($this->checkWin()) {
            return self::WIN;
        }
        if ($this->checkDraw()) {
            return  self::DRAW;
        }
        return self::GAME;
    }

}
