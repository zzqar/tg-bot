<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::MEME)]
class BenderBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'bender';
    }
    public function getDescription(): string
    {
        return 'база от бендера';
    }

    public function execute(Message $message, Client $client): void
    {
        $line = $this->getRandomPhrase('bender');
        $media = new ArrayOfInputMedia();
        $media->addItem(
            new InputMediaPhoto("https://apimeme.com/meme?meme=Bender&top={$line}&bottom=")
        );
        $client->sendMediaGroup($message->getChat()->getId(), $media);
    }


}
