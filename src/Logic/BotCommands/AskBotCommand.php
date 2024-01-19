<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use Orhanerday\OpenAi\OpenAi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class AskBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'ask';
    }
    public function getDescription(): string
    {
        return 'gpt-3.5 (not supported)';
    }

    public function isHide(): bool
    {
        return true;
    }

    public function execute(Message $message, Client $client): void
    {
        $args = $this->getText();
        if (empty($args)) {
            $client->sendMessage($message->getChat()->getId(), 'Юзай /ask твой тупой вопрос');
            return;
        }

        $open_ai = new OpenAi($_ENV['OPENAI_KEY']);

        $chat = $open_ai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    "role" => "system",
                    "content" => "You are a helpful assistant."
                ],
                [
                    "role" => "user",
                    "content" => "Who won the world series in 2020?"
                ],
                [
                    "role" => "assistant",
                    "content" => "The Los Angeles Dodgers won the World Series in 2020."
                ],
                [
                    "role" => "user",
                    "content" => "Where was it played?"
                ],
            ],
            'temperature' => 1.0,
            'max_tokens' => 4000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);

    }


}
