<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class EggBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'egg';
    }

    public function getDescription(): string
    {
        return 'база';
    }
    public function execute(Message $message, Client $client): void
    {
        $userName = $message->getFrom()->getUsername();
        $text = str_replace('%user%', "@{$userName}", $this->getRandomPhrase('egg'));
        $client->sendMessage($message->getChat()->getId(), $text);
    }


}
