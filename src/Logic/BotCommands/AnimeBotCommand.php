<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia;
use TelegramBot\Api\Types\InputMedia\InputMediaVideo;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::INFO)]
class AnimeBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'anime';
    }

    public function getDescription(): string
    {
        return 'АНИМЭ';
    }
    public function execute(Message $message, Client $client): void
    {
        $cats = (new \GuzzleHttp\Client())
            ->get('https://nekos.best/api/v2/endpoints')
            ->getBody()
            ->getContents();
        $key = array_rand(json_decode($cats, true));

        $image = (new \GuzzleHttp\Client())
            ->get("https://nekos.best/api/v2/{$key}")
            ->getBody()
            ->getContents();

        $data = json_decode($image, true);


        $media = new ArrayOfInputMedia();
        $media->addItem(
            new InputMediaVideo($data['results'][0]['url'], $data['results'][0]['anime_name'])
        );
        $client->sendMediaGroup($message->getChat()->getId(), $media);
    }


}
