<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::MEME)]
class StathamBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'statham';
    }

    public function getDescription(): string
    {
        return 'база от statham';
    }

    public function execute(Message $message, Client $client): void
    {
        $meme = (new \GuzzleHttp\Client())
            ->get('http://statham.dbapp.ru/api.php')
            ->getBody()
            ->getContents();

        $data = json_decode($meme, true);
        $meme = random_int(0, (count($data['memes']) - 1));

        $media = new ArrayOfInputMedia();
        $media->addItem(
            new InputMediaPhoto("http://statham.dbapp.ru{$data['memes'][$meme]}")
        );
        $client->sendMediaGroup($message->getChat()->getId(), $media);
    }


}
