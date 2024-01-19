<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class IdBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'id';
    }

    public function getDescription(): string
    {
        return 'id';
    }

    public function execute(Message $message, Client $client): void
    {
        $txt = [
            'ID:',
            $message->getChat()->getId(),
        ];

        $client->sendMessage($message->getChat()->getId(), implode("\n", $txt));
    }


}
