<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Game\TicTac\TicTacGame;
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
        $ticTac = new TicTacGame;
        if ($this->getParamByKey('clear')){
            $ticTac->clearStats();
            $client->deleteMessage($message->getChat()->getId(), $message->getMessageId());
            return;
        }

        if ($id = $ticTac->getMessByChatId($message->getChat()->getId())) {
            try {
                $client->deleteMessage($message->getChat()->getId(), $id);
            } catch (\Throwable $e) {}
        }
        $response = $ticTac->renderByState();
        /** @var Message $mess */
        $mess = $this->sendResponse($client, $message, $response);
        $ticTac->setMessId($message->getChat()->getId(), $mess->getMessageId());

    }
}
