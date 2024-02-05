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
                ['text' => 'Своя', 'callback_data' => '/tic_stats_self'],
                ['text' => 'Выбрать игрока', 'callback_data' => '/tic_stats_select']
            ],
            [
                ['text' => 'Общая', 'callback_data' => '/tic_stats_all']
            ],
            [
                ['text' => 'В меню', 'callback_data' => '/tic_menu']
            ],
        ]);
    }

    public static function menu(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => '1 на 1', 'callback_data' => '/tic_search']
            ],
            [
                ['text' => 'C ботом', 'callback_data' => '/tic_bot_menu']
            ],
            [
                ['text' => 'Статистика', 'callback_data' => '/tic_stats']
            ],
        ]);
    }

    public static function search(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => 'Учавствовать', 'callback_data' => '/tic_register'],
                ['text' => 'Перезапустить', 'callback_data' => '/tic_search']
            ],
            [
                ['text' => 'В меню', 'callback_data' => '/tic_menu']
            ],

        ]);
    }

    public static function game(Board $board): InlineKeyboardMarkup
    {
        $key = [];
        foreach ($board->getBoard() as $y => $line) {
            $lineKey = [];
            foreach ($line as $x => $item) {
                if ($item === BoardValue::null) {
                    $lineKey[] = ['text' => "🟩", 'callback_data' => "/tic_move -x=$x -y=$y"];
                } else {
                    $lineKey[] = ['text' => "🔒", 'callback_data' => "/tic_move -block=1 "];
                }
            }
            $key[] = $lineKey;
        }
        $key[] = [['text' => 'В меню', 'callback_data' => '/tic_menu']];
        return new InlineKeyboardMarkup($key);
    }

    public static function finish(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => 'В меню', 'callback_data' => '/tic_menu']
            ],
        ]);
    }

    public static function statsSelf(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => 'Назад', 'callback_data' => '/tic_stats'],
                ['text' => 'В меню', 'callback_data' => '/tic_menu']
            ],
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
            ['text' => 'Назад', 'callback_data' => '/tic_stats'],
            ['text' => 'В меню', 'callback_data' => '/tic_menu']
        ];
        return new InlineKeyboardMarkup($inlineArray);
    }

    public static function bot(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => 'Анрил', 'callback_data' => '/tic_bot_start -lvl=100']
            ],
            [
                ['text' => 'Выше среднего', 'callback_data' => '/tic_bot_start -lvl=75']
            ],
            [
                ['text' => 'Нормуль', 'callback_data' => '/tic_bot_start -lvl=50']
            ],
            [
                ['text' => 'Слишком просто', 'callback_data' => '/tic_bot_start -lvl=25']
            ],
            [
                ['text' => 'Для детей', 'callback_data' => '/tic_bot_start -lvl=0']
            ],
            [
                ['text' => 'В меню', 'callback_data' => '/tic_menu']
            ],
        ]);

    }
}
