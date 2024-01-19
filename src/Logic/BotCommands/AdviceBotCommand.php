<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::INFO)]
class AdviceBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'advice';
    }
    public function getDescription(): string
    {
        return 'говно-совет';
    }

    public function execute(Message $message, Client $client): void
    {
        $advice = (new \GuzzleHttp\Client())
            ->get('https://api.adviceslip.com/advice')
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
                    'q' => $array['slip']['advice'],
                ],
            ])
            ->getBody()
            ->getContents();

        $array = json_decode($translate, true, 512, JSON_THROW_ON_ERROR);
        $client->sendMessage($message->getChat()->getId(), $array[0][0][0]);
    }


}
