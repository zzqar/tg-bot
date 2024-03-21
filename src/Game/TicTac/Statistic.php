<?php

namespace App\Game\TicTac;

use App\Exceptions\GameException;
use Throwable;

class Statistic
{
    /**
     *   [
     *      'stats' => [],
     *      'settings' => [
     *          'board' => ...(int $size),
     *
     *      ],
     *   ]
     */
    protected const STATS_FILENAME = 'tictac_stats.json';

    public function __construct()
    {
    }

    public static function getStats()
    {
        $data = static::getData();
        return $data['stats'];
    }

    protected static function getData()
    {
        try {
            return json_decode(
                file_get_contents(static::STATS_FILENAME),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @throws GameException
     */
    protected static function saveData($data): void
    {
        try {
            file_put_contents(
                static::STATS_FILENAME,
                json_encode($data, JSON_THROW_ON_ERROR)
            );
        } catch (Throwable $e) {
            throw new GameException($e->getMessage());
        }
    }
    public static function getBoard(): int
    {
        return static::getSettings()['board'];
    }
    public static function getSettings(): array
    {
        return static::getData()['settings'];
    }

    /**
     * @throws GameException
     */
    public static function setBoard(int $board): void
    {
        $data = static::getData();
        $data['settings']['board'] = $board;
        static::saveData($data);
    }

    /**
     * @throws GameException
     */
    public static function setStats(array $stats): void
    {
        $data = static::getData();
        $data['stats'] = $stats;
        static::saveData($data);
    }

}
