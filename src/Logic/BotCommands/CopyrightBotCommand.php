<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;


class CopyrightBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'copyright';
    }

    public function getDescription(): string
    {
        return 'типо права';
    }

    public function execute(Message $message, Client $client): void
    {
        $txt = [
            'zzqar & Noa Naoki',
            'ООО «ИДИНАХУЙ»',
            'https://djnoanaoki.ru/',
            'source:',
            'https://github.com/pdjshog/tgfunbot',
            'https://github.com/zzqar',
        ];

        $client->sendMessage($message->getChat()->getId(), implode("\n", $txt));
    }


}
