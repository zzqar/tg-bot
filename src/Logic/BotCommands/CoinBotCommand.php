<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::GAME)]
class CoinBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'coin';
    }

    public function getDescription(): string
    {
        return 'бросить монетку';
    }

    public function execute(Message $message, Client $client): void
    {
        $rand = random_int(0, 110);
        if ($rand > 105) {
            $text = 'укатилась';
        } elseif ($rand > 100) {
            $text = 'ребро';
        } elseif ($rand > 50) {
            $text = 'решка';
        } else {
            $text = 'орел';
        }


        $client->sendMessage($message->getChat()->getId(), "🪙{$text}");
    }


}
