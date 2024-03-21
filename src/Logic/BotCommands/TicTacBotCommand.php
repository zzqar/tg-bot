<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Exceptions\GameException;
use App\Game\TicTac\GameRender;
use App\Game\TicTac\Statistic;
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

    /**
     * @throws GameException
     */
    public function execute(Message $message, Client $client): void
    {

        if ($this->getParamByKey('cash') === 'clear'){
            TicTacGame::clearCache();
            $client->deleteMessage($message->getChat()->getId(), $message->getMessageId());
            return;
        }

        if ($this->getParamByKey('clear')){
            Statistic::setStats([]);
            $client->deleteMessage($message->getChat()->getId(), $message->getMessageId());
            return;
        }

        $ticTac = new TicTacGame;

        if ($id = $ticTac->getMessByChatId($message->getChat()->getId())) {
            try {
                $client->deleteMessage($message->getChat()->getId(), $id);
            } catch (\Throwable $e) {}
        }
        $response = (new GameRender($ticTac))->render();
        /** @var Message $mess */
        $mess = $this->sendResponse($client, $message, $response);
        $ticTac->setMessId($message->getChat()->getId(), $mess->getMessageId());

    }
}
