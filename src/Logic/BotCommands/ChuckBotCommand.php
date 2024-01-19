<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::MEME)]
class ChuckBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'chuck';
    }

    public function getDescription(): string
    {
        return 'Чак Норис';
    }

    public function execute(Message $message, Client $client): void
    {
        $advice = (new \GuzzleHttp\Client())
            ->get('https://geek-jokes.sameerkumar.website/api?format=json')
            ->getBody()
            ->getContents();

        $array = json_decode($advice, true);
        $translate = (new \GuzzleHttp\Client())
            ->post('https://translate.googleapis.com/translate_a/single', [
                'query' => [
                    'client' => 'gtx',
                    'sl' => 'en',
                    'tl' => 'ru',
                    'dt' => 't',
                    'q' => $array['joke'],
                ],
            ])
            ->getBody()
            ->getContents();

        $array = json_decode($translate, true);
        $client->sendMessage($message->getChat()->getId(), $array[0][0][0]);
    }


}
