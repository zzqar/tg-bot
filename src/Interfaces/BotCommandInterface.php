<?php

namespace App\Interfaces;

use App\Attribute\TypeCommand;
use App\Exceptions\NotFoundApiException;
use App\Trait\Bot;
use Exception;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::ALL)]
abstract class BotCommandInterface
{
    use Bot;
    abstract public function getCommand(): string;

    abstract public function getDescription(): string;

    abstract public function execute(Message $message, Client $client): void;

    public function isHide(): bool
    {
        return false;
    }

    /**
     * @return string[]
     * @throws NotFoundApiException
     */
    public function getPhrases(string $name): array
    {
        $file = "Phrases/" . $name . ".txt";
        if (!is_file($file)) {
            throw new NotFoundApiException($file);
        }
        return file($file);
    }

    /**
     * @param string $name
     * @return string
     * @throws NotFoundApiException
     * @throws Exception
     */
    protected function getRandomPhrase(string $name): string
    {
        $phrases = $this->getPhrases($name);

        $line = $phrases[random_int(0, count($phrases) - 1)];
        return str_replace("\n", '', $line);
    }
}
