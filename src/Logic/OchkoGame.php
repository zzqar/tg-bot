<?php

namespace App\Logic;

use App\Trait\HTML;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\User;

/**
 *
 */
class OchkoGame
{
    use HTML;

    /**
     * @var FilesystemAdapter
     */
    protected $cache;

    /**
     * @var string
     */
    protected string $none = '🂠';

    /**
     * @var array|string[]
     */
    protected array $cards = [
        2 => '🂫',
        3 => '🂭',
        4 => '🂮',
        5 => '🂥',
        6 => '🂦',
        7 => '🂧',
        8 => '🂨',
        9 => '🂩',
        10 => '🂪',
        11 => '🂡',
    ];

    /**
     * @var array|int[]
     */
    protected array $cardsVars = [
        2, 2, 2, 2,
        3, 3, 3, 3,
        4, 4, 4, 4,
        5, 5, 5, 5,
        6, 6, 6, 6,
        7, 7, 7, 7,
        8, 8, 8, 8,
        9, 9, 9, 9,
        10, 10, 10, 10,
        11, 11, 11, 11,
    ];

    /**
     * @param Client $client
     * @param Message $message
     */
    public function __construct(
        private Client  $client,
        private Message $message
    )
    {
        $this->cache = new FilesystemAdapter();
    }


    /**
     * @param bool $stat
     * @return InlineKeyboardMarkup
     */
    private function keyboard(bool $stat = false): InlineKeyboardMarkup
    {
        $keyboard = [
            [
                ['text' => 'Еще', 'callback_data' => '/omore ']
            ]
        ];

        if ($stat) {
            $keyboard[] = [
                ['text' => 'Статистика', 'callback_data' => '/stat ']
            ];
        } else {
            $keyboard[] = [
                ['text' => 'Хорош', 'callback_data' => '/odone ']
            ];
        }

        return new InlineKeyboardMarkup($keyboard);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function runGame(User $user, $messID = null): void
    {
        try {
            $usersSaved = json_decode(file_get_contents('ochko.json'), true);

        } catch (\Throwable $exception) {
            $usersSaved = [];
        }
        $text = [];

        $uname = $user->getUsername();
        if (empty($usersSaved[$uname])) {
            $usersSaved[$uname] = [
                'win' => 0,
                'loose' => 0,
            ];
        }

        $wins = $usersSaved[$uname]['win'] ?? 0;
        $looses = $usersSaved[$uname]['loose'] ?? 0;

        $text[] = "Побед {$wins} / Проебов {$looses}";
        $text[] = str_repeat("-", 60);

        $text = array_merge($text, [
            'Правила:',
            'Нажми еще чтобы получить карту',
            'Нажми хорош и мы сравним результаты'
        ]);

        if ($messID) {
            $this->client->editMessageText(
                $this->message->getChat()->getId(),
                $messID,
                implode("\n", $text),
                null,
                false,
                $this->keyboard(true)
            );
        } else {
            /** @var Message $mess */
            $mess = $this->client->sendMessage(
                $this->message->getChat()->getId(),
                implode("\n", $text),
                null,
                false,
                null,
                $this->keyboard(true)
            );
            $messID = $mess->getMessageId();
        }
        $this->sessionSet($messID, $uname);
    }

    /**
     * @param array $values
     * @param bool $showScore
     * @param string $text
     * @return string
     */
    private function renderCards(array $values, bool $showScore = true, string $text = 'Мои карты')
    {
        $result = $text . '(' . ($showScore ? array_sum($values) : '??') . "): \n";
        foreach ($values as $score) {
            $result .= $showScore ? $this->cards[$score] : $this->none;
        }
        return $result;
    }

    /**
     * @param $messageID
     * @param $user
     * @return array
     * @throws InvalidArgumentException
     */
    protected function sessionSet($messageID, $user)
    {
        $cacheItem = $this->cache->getItem('ochko_session_' . $messageID);
        $session = $cacheItem->get() ?: [];
        $session['user'] = $user;
        $cacheItem->set($session);
        $this->cache->save($cacheItem);
        return $session;
    }

    /**
     * @param $messageID
     * @param $user
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function isMySession($messageID, $user): bool
    {
        $cacheItem = $this->cache->getItem('ochko_session_' . $messageID);
        $session = $cacheItem->get() ?: [];
        if (empty($session)) {
            return true;
        }
        return $session['user'] == $user;
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    protected function get()
    {
        $cacheItem = $this->cache->getItem('ochko_' . $this->message->getMessageId());
        return $cacheItem->get() ?: [];
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    protected function delete()
    {
        $this->cache->deleteItem('ochko_' . $this->message->getMessageId());
        $this->cache->deleteItem('ochko_session_' . $this->message->getMessageId());
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    protected function up()
    {
        $cacheItem = $this->cache->getItem('ochko_' . $this->message->getMessageId());
        $ochko = $cacheItem->get() ?: [];

        $ochko['player'][] = $this->cardsVars[array_rand($this->cardsVars)];
        $botRand = $this->cardsVars[array_rand($this->cardsVars)];

        $botSum = array_sum($ochko['bot'] ?? []);
        if ($botRand + $botSum <= 21) {
            $ochko['bot'][] = $botRand;
        }


        $cacheItem->set($ochko);
        $this->cache->save($cacheItem);

        return $ochko;
    }

    /**
     *
     */
    const PLAYER_LOOSE = 1;
    /**
     *
     */
    const PLAYER_WIN = 2;
    /**
     *
     */
    const NEUTRAL = 3;
    /**
     *
     */
    const GAME_CONT = 0;


    /**
     * @param int $player
     * @param int $bot
     * @return int
     */
    public function checkScore(int $player, int $bot)
    {
        if ($player > 21) {
            return self::PLAYER_LOOSE;
        }

        if ($bot > 21) {
            return self::PLAYER_WIN;
        }

        if ($player == 21 && $bot == 21) {
            return self::NEUTRAL;
        }

        if ($player == $bot) {
            return self::NEUTRAL;
        }

        if ($player > $bot) {
            return self::PLAYER_WIN;
        }

        if ($player < $bot) {
            return self::PLAYER_LOOSE;
        }

        if ($player == 21) {
            return self::PLAYER_WIN;
        }

        if ($bot == 21) {
            return self::PLAYER_LOOSE;
        }

        return self::GAME_CONT;
    }

    /**
     * @param CallbackQuery $callbackQuery
     * @param bool $up
     * @return void
     * @throws InvalidArgumentException
     */
    public function more(CallbackQuery $callbackQuery, bool $up = true)
    {
        if (!$this->isMySession(
            $callbackQuery->getMessage()->getMessageId(),
            $callbackQuery->getFrom()->getUsername())
        ) {

            $this->client->answerCallbackQuery(
                $callbackQuery->getId(),
                'Может ты нахуй сходишь ? я не с тобой играю пидор!'
            );
            return;
        }

        $scores = $up ? $this->up() : $this->get();
        $pScore = array_sum($scores['player'] ?? []);
        $bScore = array_sum($scores['bot'] ?? []);

        try {
            $usersSaved = json_decode(file_get_contents('ochko.json'), true);

        } catch (\Throwable $exception) {
            $usersSaved = [];
        }

        if ($bScore >= 21 || $pScore >= 21 || !$up) {
            $result = $this->checkScore($pScore, $bScore);
        } else {
            $result = self::GAME_CONT;
        }

        $text = [];

        $botScore = false;
        if (empty($usersSaved[$callbackQuery->getFrom()->getUsername()])) {
            $usersSaved[$callbackQuery->getFrom()->getUsername()] = [
                'win' => 0,
                'loose' => 0,
            ];
        }

        $wins = $usersSaved[$callbackQuery->getFrom()->getUsername()]['win'] ?? 0;
        $looses = $usersSaved[$callbackQuery->getFrom()->getUsername()]['loose'] ?? 0;

        switch ($result) {
            case self::PLAYER_WIN:
                $botScore = true;
                $usersSaved[$callbackQuery->getFrom()->getUsername()]['win']++;
                $wins++;
                $text[] = 'Ты победилдо';
                $text[] = "Побед {$wins} / Проебов {$looses}";
                break;

            case self::PLAYER_LOOSE:
                $botScore = true;
                $looses++;
                $usersSaved[$callbackQuery->getFrom()->getUsername()]['loose']++;
                $text[] = 'Ты проебал';
                $text[] = "Побед {$wins} / Проебов {$looses}";

                break;

            case self::NEUTRAL:
                $botScore = true;
                $text[] = 'Ничья';
                $text[] = "Побед {$wins} / Проебов {$looses}";
                break;

        }

        $text[] = $this->renderCards($scores['bot'], $botScore);
        $text[] = $this->renderCards($scores['player'], true, 'Твои карты');

        $this->client->editMessageText(
            $this->message->getChat()->getId(),
            $callbackQuery->getMessage()->getMessageId(),
            implode("\n", $text),
            "Markdown",
            false,
            $botScore ? null : $this->keyboard()
        );


        if ($botScore) {
            file_put_contents('ochko.json', json_encode($usersSaved));
            $this->delete();
        }
    }

    /**
     * @param CallbackQuery $call
     * @return void
     */
    public function stat(CallbackQuery $call)
    {
        try {
            $usersSaved = json_decode(file_get_contents('ochko.json'), true);

        } catch (\Throwable $exception) {
            $usersSaved = [];
        }
        $text[] = 'Статистика';

        foreach ($usersSaved as $name => $usr) {
            $wins = $usr['win'] ?? 0;
            $looses = $usr['loose'] ?? 0;

            $text[] = "{$name}: Побед {$wins} / Проебов {$looses}";
        }

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Назад', 'callback_data' => '/back ']
            ],
        ]);
        $this->client->editMessageText(
            $this->message->getChat()->getId(),
            $call->getMessage()->getMessageId(),
            implode("\n", $text),
            "Markdown",
            false,
            $keyboard
        );


    }

}
