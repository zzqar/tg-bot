<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Game\TicTacGame;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::GAME)]
class TicTacBotCommand extends BotCommandInterface
{

    public function getCommand(): string
    {
        return 'tictac';
    }

    public function getDescription(): string
    {
        return 'крестики нолики';
    }

    public function execute(Message $message, Client $client): void
    {
        $ticTac = new TicTacGame();
        if ($id = $ticTac->getMessByChatId($message->getChat()->getId())) {
            $client->deleteMessage($message->getChat()->getId(), $id);
        }
        $response = $ticTac->search();
        /** @var Message $mess */
        $mess = $client->sendMessage(
            $message->getChat()->getId(),
            $response->getText(),
            "Markdown",
            false,
            null,
            $response->getInlineKeyboard()
        );
        $ticTac->setMessId($message->getChat()->getId(), $mess->getMessageId());

    }
}
