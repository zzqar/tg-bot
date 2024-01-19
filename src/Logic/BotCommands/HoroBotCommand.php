<?php

namespace App\Logic\BotCommands;

use App\Api\HoroApi;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;

class HoroBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'horo';
    }

    public function getDescription(): string
    {
        return 'для любителей астрологии';
    }

    public function execute(Message $message, Client $client): void
    {
        $text = $this->getText();
        $znaki = HoroApi::ZNAKI;
        $key = array_search(mb_strtolower($text), $znaki, true);

        if ($key) {
            $result[] = "<strong>Гороскоп: {$znaki[$key]}</strong>";
            $result[] = HoroApi::getByKey($key);
            $client->sendMessage(
                $message->getChat()->getId(),
                implode("\n", $result),
                'html'
            );
        } else {
            $result[] = "<strong>Гороскоп на сегодня</strong>";
            $result[] = "Выберите знак, гороскоп которого вы хотите узнать.";
            $client->sendMessage(
                $message->getChat()->getId(),
                implode("\n", $result),
                'html',
                false,
                null,
                $this->getKeyboard()
            );
        }
    }

    protected function getKeyboard(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup(
            [
                [
                    ['text' => 'Овен', 'callback_data' => '/horo aries'],
                    ['text' => 'Телец', 'callback_data' => '/horo taurus'],
                    ['text' => 'Близнецы', 'callback_data' => '/horo gemini'],
                    ['text' => 'Рак', 'callback_data' => '/horo cancer'],
                ],
                [
                    ['text' => 'Лев', 'callback_data' => '/horo leo'],
                    ['text' => 'Дева', 'callback_data' => '/horo virgo'],
                    ['text' => 'Весы', 'callback_data' => '/horo libra'],
                    ['text' => 'Скорпион', 'callback_data' => '/horo scorpio'],
                ],
                [
                    ['text' => 'Стрелец', 'callback_data' => '/horo sagittarius'],
                    ['text' => 'Козерог', 'callback_data' => '/horo capricorn'],
                    ['text' => 'Водолей', 'callback_data' => '/horo aquarius'],
                    ['text' => 'Рыбы', 'callback_data' => '/horo pisces'],
                ]
            ]
        );
    }

}
