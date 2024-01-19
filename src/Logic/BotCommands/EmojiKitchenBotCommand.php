<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use CURLFile;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class EmojiKitchenBotCommand extends BotCommandInterface
{
    protected $size = '300';

    public function getCommand(): string
    {
       return 'kitchen';
    }

    public function getDescription(): string
    {
       return 'emoji kitchen';
    }

    public function execute(Message $message, Client $client): void
    {
        $emoji = $this->getText();
        if ($emoji === null || !str_contains($emoji, '_')) {
            $client->sendMessage($message->getChat()->getId(), 'Юзай /kitchen 🥹_😗');
            return;
        }
        $error = [];
        try {
            $qrSticker = new CURLFile("https://emojik.vercel.app/s/{$emoji}?size={$this->size}");
            $client->sendSticker($message->getChat()->getId(), $qrSticker);
        } catch (\Exception $e) {

            $error[] = 'Что то пошло не по плану';
            $error[] = $e->getMessage();
            $error[] = $qrSticker->getFilename();

            $client->sendMessage($message->getChat()->getId(),  implode("\n", $error));
        }

    }
}
