<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::TEST)]
class IqBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'iq';
    }

    public function getDescription(): string
    {
        return 'узнай свой iq';
    }

    public function execute(Message $message, Client $client): void
    {
        $userName = $message->getFrom()->getUsername();
        $rand = random_int(0, 160);
        if ($userName == 'noanaoki') {
            $client->sendMessage($message->getChat()->getId(), "Еба, зашкалило, почти 50");
        } else {
            $client->sendMessage($message->getChat()->getId(), "У @{$userName} IQ на {$rand}, инфа сотка.");
        }
    }


}
