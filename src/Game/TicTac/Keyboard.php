<?php

namespace App\Game\TicTac;

use App\Game\TicTac\Enum\BoardValue;
use App\Game\TicTac\GameBoard\Board;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Keyboard
{
    public static function menuStats(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => '👤 Своя', 'callback_data' => '/tic_stats_self'],
            ],
            [
                ['text' => '🔎 Выбрать', 'callback_data' => '/tic_stats_select'],
                ['text' => '👥 Общая', 'callback_data' => '/tic_stats_all']
            ],
            [
               static::inMenu()
            ],
        ]);
    }

    public static function settings(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => 'Выбрать доску', 'callback_data' => '/tic_setting_board_size_select'],
            ],
            [
                static::inMenu()
            ],
        ]);
    }

    public static function menu(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => '⚔️ 1 на 1', 'callback_data' => '/tic_search'],
                ['text' => '🤖 C ботом', 'callback_data' => '/tic_bot_menu']
            ],
            [
                ['text' => '⚙️ Настройки', 'callback_data' => '/tic_setting']
            ],
            [
                ['text' => '📊 Статистика', 'callback_data' => '/tic_stats']
            ],
        ]);
    }

    public static function search(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => '💅 Учавствовать', 'callback_data' => '/tic_register'],
                ['text' => '🔃 Перезапустить', 'callback_data' => '/tic_search']
            ],
            [static::inMenu()],

        ]);
    }

    public static function game(Board $board): InlineKeyboardMarkup
    {
        $key = [];
        foreach ($board->getBoard() as $y => $line) {
            $lineKey = [];
            foreach ($line as $x => $item) {
                $lineKey[] = ($item === BoardValue::null)
                    ? ['text' => "🟩", 'callback_data' => "/tic_move -x=$x -y=$y"]
                    : ['text' => "🔒", 'callback_data' => "/tic_move -block=1"];
            }
            $key[] = $lineKey;
        }
        $key[] = [static::inMenu()];
        return new InlineKeyboardMarkup($key);
    }

    public static function finish(bool $bot = false): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => '🔃 Заново', 'callback_data' => $bot ? '/tic_bot_menu' : '/tic_search'],
                static::inMenu()
            ],
        ]);
    }

    public static function statsSelf(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => '◀️ Назад', 'callback_data' => '/tic_stats'],
                static::inMenu()
            ]
        ]);
    }

    public static function boardSizeSettings(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => '3 на 3', 'callback_data' => '/tic_set_board_size -size=3'],
            ],
            [
                ['text' => '4 на 4', 'callback_data' => '/tic_set_board_size -size=4'],
            ],
            [
                ['text' => '◀️ Назад', 'callback_data' => '/tic_setting'],
                static::inMenu()
            ]
        ]);
    }

    public static function statsSelect($arrayName): InlineKeyboardMarkup
    {
        $keys = array_map(
            static fn($name) => ['text' => $name, 'callback_data' => "/tic_stats_target -name=$name"],
            $arrayName
        );
        $inlineArray = array_chunk($keys, 3);
        $inlineArray[] = [
            ['text' => '◀️ Назад', 'callback_data' => '/tic_stats'],
            static::inMenu()
        ];
        return new InlineKeyboardMarkup($inlineArray);
    }

    public static function bot(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => '⚠️ Анрил', 'callback_data' => '/tic_bot_start -lvl=100'],
                ['text' => '😈 Нормуль', 'callback_data' => '/tic_bot_start -lvl=75']
            ],
            [
                ['text' => '🤠 Просто', 'callback_data' => '/tic_bot_start -lvl=50'],
                ['text' => '♿️ Изи', 'callback_data' => '/tic_bot_start -lvl=25'],
                ['text' => '🚼 Детеский', 'callback_data' => '/tic_bot_start -lvl=0']
            ],
            [static::inMenu()],
        ]);
    }

    protected static function inMenu(): array
    {
        return ['text' => '🏠 В меню', 'callback_data' => '/tic_menu'];
    }

}
