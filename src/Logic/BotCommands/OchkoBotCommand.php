<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use App\Logic\OchkoGame;
use Psr\Cache\InvalidArgumentException;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::GAME)]
class OchkoBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'ochko';
    }

    public function getDescription(): string
    {
        return 'сыграть в очко';
    }

    /**
     * @throws InvalidArgumentException
     */
    public function execute(Message $message, Client $client): void
    {
        (new OchkoGame($client, $message))->runGame($message->getFrom());
    }


}
