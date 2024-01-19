<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use TelegramBot\Api\Types\Message;

class DuckBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'duck';
    }

    public function getDescription(): string
    {
        return 'утка';
    }

    public function execute(Message $message, Client $client): void
    {
        $meme = (new \GuzzleHttp\Client())
            ->get('https://random-d.uk/api/v2/random')
            ->getBody()
            ->getContents();

        $data = json_decode($meme, true);

        $media = new ArrayOfInputMedia();
        $media->addItem(
            new InputMediaPhoto($data['url'])
        );
        $client->sendMediaGroup($message->getChat()->getId(), $media);
    }


}
