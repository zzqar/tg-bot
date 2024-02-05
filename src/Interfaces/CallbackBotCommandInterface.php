<?php

namespace App\Interfaces;

use App\Helpers\GameResponse;
use App\Helpers\Response;
use App\Trait\Bot;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Message;

abstract class CallbackBotCommandInterface
{
    use Bot;

    protected string $actionCommand;

    /** @var Client */
    protected Client $client;

    /** @var CallbackQuery  */
    protected CallbackQuery $callback;
    protected string $parseMode = 'Markdown';


    public function __construct(
        Client $client,
        CallbackQuery $callback,
        string $command
    )
    {
        $this->client = $client;
        $this->callback = $callback;
        $this->actionCommand = $command;
        $this->setArgumentsByText($command, $this->callback->getData());
    }

    public function getMessage(): Message
    {
        return $this->callback->getMessage();
    }

    public function getChatId(): string|int|float
    {
        return $this->getMessage()->getChat()->getId();
    }

    public function deleteMessage($messId): void
    {
        $this->client->deleteMessage($this->getChatId(), $messId);
    }

    public function clearKeybord()
    {
        $this->client->editMessageReplyMarkup(
            $this->getChatId(),
            $this->getMessageId(),
        );
    }

    public function getMessageId(): float|int
    {
        return $this->getMessage()->getMessageId();
    }

    public function renderResponse(Response $response): void
    {
        if ($response instanceof GameResponse) {
            $this->client->editMessageText(
                $this->getChatId(),
                $this->getMessageId(),
                $response->getText(),
                $this->parseMode,
                false,
                $response->getInlineKeyboard()
            );
        } else {
            $this->client->answerCallbackQuery(
                $this->callback->getId(),
                $response->getText()
            );
        }
    }

}
