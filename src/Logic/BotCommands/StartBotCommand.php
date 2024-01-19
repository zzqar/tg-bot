<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class StartBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'start';
    }
    public function getDescription(): string
    {
        return 'start';
    }
    public function isHide(): bool
    {
        return true;
    }

    public function execute(Message $message, Client $client): void
    {
        $txt = [
            'Бомжур ебать!',
            "ID: {$message->getChat()->getId()}",
        ];

        $client->sendMessage($message->getChat()->getId(), implode("\n", $txt));
    }


}
