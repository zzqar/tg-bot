<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use GuzzleHttp\Exception\GuzzleException;
use TelegramBot\Api\Client;
use TelegramBot\Api\Collection\KeyHasUseException;
use TelegramBot\Api\Collection\ReachedMaxSizeException;
use TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::MEME)]
class MemeBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'meme';
    }

    public function getDescription(): string
    {
        return 'база';
    }

    /**
     * @throws GuzzleException
     * @throws KeyHasUseException
     * @throws ReachedMaxSizeException
     */
    public function execute(Message $message, Client $client): void
    {
        $text = $this->getText('pikabu');

        $meme = (new \GuzzleHttp\Client([
            'timeout' => 15,
        ]))->get('https://meme-api.com/gimme/' . $text)
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
