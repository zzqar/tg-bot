<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Game\LabyrinthGame;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::GAME)]
class LabyrinthBotCommand extends BotCommandInterface
{

    public function getCommand(): string
    {
        return 'lab';
    }

    public function getDescription(): string
    {
        return "лабиринт";
    }

    public function execute(Message $message, Client $client): void
    {
        $response = (new LabyrinthGame())->menu();
        $client->sendMessage(
            $message->getChat()->getId(),
            $response->getText(),
            "Markdown",
            false,
            null,
            $response->getInlineKeyboard()
        );
    }
}
