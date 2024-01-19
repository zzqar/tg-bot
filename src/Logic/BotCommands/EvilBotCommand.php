<?php

namespace App\Logic\BotCommands;

use App\Api\EvilApi;
use App\Interfaces\BotCommandInterface;
use GuzzleHttp\Exception\GuzzleException;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\User;

class EvilBotCommand extends BotCommandInterface
{

    public function getCommand(): string
    {
        return 'evil';
    }
    public function getDescription(): string
    {
        return 'злой? юзай';
    }

    /**
     * Должен высирать оскорбление
     *
     * @param Message $message
     * @param Client $client
     * @return void
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function execute(Message $message, Client $client): void
    {
        /** @var User $user */
        $user = $message->getFrom();

        if (!$username = $this->getText()) {
            $username = $user->getFirstName() ?: $user->getUsername() ?: 'Петуч';
        }

        $param = $this->getParams();

        $ln = $param['ln'] ?? null;
        $text = $username . ', ' . (new EvilApi($ln))->getResponse();
        $client->sendMessage($message->getChat()->getId(), $text);
       // $client->sendMessage($message->getChat()->getId(), $ln);
    }
}
