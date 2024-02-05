<?php

namespace App\Logic\BotCommands;

use App\Api\GdeposylkaApi;
use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use GuzzleHttp\Exception\GuzzleException;
use MathieuViossat\Util\ArrayToTextTable;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use Throwable;

#[TypeCommand(TypeCommand::INFO)]
class PochtaBotCommand extends BotCommandInterface
{

    public function getCommand(): string
    {
        return 'gde';
    }

    public function getDescription(): string
    {
        return 'трекер посылок';
    }

    /**
     * @throws \JsonException
     */
    public function execute(Message $message, Client $client): void
    {
        $tracker = $this->getText();
        $data = (new GdeposylkaApi)->getAllInfoByTrackingId($tracker);
        $client->sendMessage(
            $message->getChat()->getId(),
            $this->textToCodeHTML(json_encode($data, JSON_PRETTY_PRINT), 'json'),
            'html'
        );

    }

}
