<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class PnhBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'pnh';
    }
    public function getDescription(): string
    {
        return '';
    }

    public function execute(Message $message, Client $client): void
    {
        $text = $this->getText();
        if (empty($text)) {
            $client->sendMessage($message->getChat()->getId(), 'pnh @username');
            return;
        }

        $text = str_replace('%user%', $text, $this->getRandomPhrase('pnh'));
        $client->sendMessage($message->getChat()->getId(), $text);
    }


}
