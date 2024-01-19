<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::GAME)]
class BonesBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'bones';
    }

    public function getDescription(): string
    {
        return 'бросить кости';
    }

    public function execute(Message $message, Client $client): void
    {
        $rand = random_int(1, 6);
        $text = [
            1 => '⚀',
            2 => '⚁',
            3 => '⚂',
            4 => '⚃',
            5 => '⚄',
            6 => '⚅',
        ];
        $client->sendMessage($message->getChat()->getId(), $text[$rand]);
    }


}
