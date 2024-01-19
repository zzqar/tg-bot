<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use TelegramBot\Api\Types\Message;

class GusiBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'gagaga';
    }

    public function getDescription(): string
    {
        return 'га-га-га';
    }

    public function execute(Message $message, Client $client): void
    {
        $meme = (new \GuzzleHttp\Client())
            ->get('http://gusi.dbapp.ru/api.php')
            ->getBody()
            ->getContents();

        $data = json_decode($meme, true);
        $meme = random_int(0, (count($data['memes']) - 1));

        $media = new ArrayOfInputMedia();
        $media->addItem(
            new InputMediaPhoto("http://gusi.dbapp.ru{$data['memes'][$meme]}")
        );
        $client->sendMediaGroup($message->getChat()->getId(), $media);
    }


}
