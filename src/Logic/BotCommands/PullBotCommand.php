<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::INFO)]
class PullBotCommand extends BotCommandInterface
{

    protected $userIds = [
        1442490395,
        503806711,
        274373788
    ];

    /**
     * @return string
     */
    public function getCommand(): string
    {
       return 'pull';
    }

    public function getDescription(): string
    {
        return 'git pull';
    }


    /**
     * @param Message $message
     * @param Client $client
     * @return void
     */
    public function execute(Message $message, Client $client): void
    {
        $user = $message->getFrom();
        if ($user === null || !in_array($user->getId(), $this->userIds, true)){
            $client->sendMessage($message->getChat()->getId(), 'тебе нельзя');
            return;
        }
        exec('git pull', $output);
        $client->sendMessage(
            $message->getChat()->getId(),
            $this->textToCodeHTML(implode("\n", $output), 'shell'),
            "html"
        );
    }
}
