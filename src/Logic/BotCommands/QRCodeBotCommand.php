<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::INFO)]
class QRCodeBotCommand extends BotCommandInterface
{
    protected $size = '512x512';

    public function getCommand(): string
    {
        return 'qr';
    }

    public function getDescription(): string
    {
        return 'генерирует QR code';
    }

    public function execute(Message $message, Client $client): void
    {
        $data  = $this->getText('хуй тебе');

        $qrSticker = new \CURLFile("https://api.qrserver.com/v1/create-qr-code/?size={$this->size}&data={$data}");
        $client->sendSticker($message->getChat()->getId(), $qrSticker);
    }
}
