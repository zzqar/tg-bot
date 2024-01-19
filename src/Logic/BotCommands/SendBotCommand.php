<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class SendBotCommand extends BotCommandInterface
{

    public function getCommand(): string
    {
        return 'send';
    }

    public function getDescription(): string
    {
        return 'помогает дебажить';
    }

    public function isHide(): bool
    {
        return true;
    }

    public function execute(Message $message, Client $client): void
    {
        $chatID = $this->getParamByKey('id', 1442490395);
        $URL = $this->getParamByKey('url');

        if ($URL !== null) {

        }
        $json = json_decode($message->toJson());
        $data = json_encode($json, JSON_PRETTY_PRINT);
        $client->sendMessage($message->getChat()->getId(),
            $this->textToCodeHTML($data, 'json'),
            'html'
        );
        $client->sendMessage($message->getChat()->getId(),
            '<a href="tg://user?id=1442490395">inline mention of a user</a>',
            'html'
        );
    }
}
