<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use Orhanerday\OpenAi\OpenAi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use TelegramBot\Api\Types\Message;

class DrawBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'draw';
    }

    public function getDescription(): string
    {
        return 'рисовалка (not supported)';
    }

    public function isHide(): bool
    {
        return true;
    }

    public function execute(Message $message, Client $client): void
    {
        $args = $this->getText();
        if (empty($args)) {
            $client->sendMessage($message->getChat()->getId(), 'Юзай /draw чтото неебическое');
            return;
        }

        $open_ai = new OpenAi($_ENV['OPENAI_KEY']);
        $result = $open_ai->image([
            "prompt" => $args,
            "n" => 4,
            "size" => "1024x1024",
            "response_format" => "url",
        ]);

        $result = json_decode($result, true);

        if ($result['error'] ?? null) {
            throw new \Exception($result['error']['message']);
        }

        $media = new ArrayOfInputMedia();
        foreach ($result['data'] as $image) {
            $media->addItem(
                new InputMediaPhoto($image['url'])
            );
        }


        $client->sendMediaGroup($message->getChat()->getId(), $media);
    }


}
