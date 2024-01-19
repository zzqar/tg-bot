<?php

namespace App\Logic\CallbackCommands;

use App\Api\HoroApi;
use App\Attribute\Command;
use App\Interfaces\CallbackBotCommandInterface;

class HoroBotCommand extends CallbackBotCommandInterface
{
    #[Command('horo')]
    public function menu()
    {
        $key = $this->getText();
        $znaki = HoroApi::ZNAKI;
        $result[] = "<strong>Гороскоп: {$znaki[$key]}</strong>";
        $result[] = HoroApi::getByKey($key);
        $this->client->sendMessage(
            $this->getChatId(),
            implode("\n", $result),
            'html'
        );
    }
}
