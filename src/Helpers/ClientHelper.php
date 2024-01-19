<?php

namespace App\Helpers;

use App\Api\EvilApi;
use App\Interfaces\BotCommandInterface;
use App\Trait\HTML;
use MathieuViossat\Util\ArrayToTextTable;
use TelegramBot\Api\Client;
use TelegramBot\Api\InvalidJsonException;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\User;
use Throwable;

class ClientHelper
{
    use HTML;
    /**
     * @throws InvalidJsonException
     */
    public function __construct(private Client $client)
    {
        $this->setNewUsersEvent();
        $this->setUserLeaveEvent();
        $this->setCommandEvents();
        $this->setCallBackEvents();
        $this->setCaptionCommandEvents();
        $this->client->run();

    }

    /**
     *  Устанавливает команды
     *
     * @return void
     */
    private function setCommandEvents(): void
    {
        $client = $this->client;

        $files = glob('../src/Logic/BotCommands/*.php');
        $commands = [];
        foreach ($files as $class) {
            $class = '\App\Logic\BotCommands\\' . pathinfo($class, PATHINFO_FILENAME);
            if (is_a($class, BotCommandInterface::class, true)) {
                $commands[] = $class;
            }
        }

        foreach ($commands as $command) {
            /**
             * @var BotCommandInterface $instance
             */
            $instance = new $command();

            $client->command($instance->getCommand(), function (Message $message) use ($client, $instance) {
                try {
                    $instance->setArgumentsByText($instance->getCommand(), $message->getText());
                    $instance->execute($message, $client);
                } catch (Throwable $exception) {
                    $renderData = [
                        ['Параметр' => "File", 'Значение' => basename($exception->getFile())],
                        ['Параметр' => "Line", 'Значение' => $exception->getLine()],
                        ['Параметр' => "Message", 'Значение' => $exception->getMessage()],
                    ];
                    $renderer = new ArrayToTextTable($renderData);
                    $client->sendMessage(
                        $message->getChat()->getId(),
                        $this->textToCodeHTML($renderer->getTable()),
                        'html'
                    );
                }
            });
        }
    }

    /**
     * @return void
     */
    private function setNewUsersEvent(): void
    {
        $client = $this->client;
        /** Зашел в чат */
        $client->on(
            function (Update $update) use ($client) {
                /** @var Message $message */
                $message = $update->getMessage();
                try {
                    $users = $message->getNewChatMembers();
                    foreach ($users as $user) {
                        $userNames[] = !empty($user->getUserName()) ? '@' . $user->getUserName() : 'Петуч';
                    }
                    $text = implode(', ', $userNames ?? ['Петуч']);
                    $client->sendMessage($message->getChat()->getId(), $text . ', ' . (new EvilApi())->getResponse());
                } catch (Throwable $exception) {
                    $client->sendMessage($message->getChat()->getId(), $exception->getMessage());
                }
            },
            function (Update $update) {
                return !(!$update->getMessage() || !$update->getMessage()->getNewChatMembers());
            }
        );
    }

    /**
     * @return void
     */
    private function setUserLeaveEvent(): void
    {
        $client = $this->client;
        /** Лив из чата */
        $client->on(
            function (Update $update) use ($client) {
                /** @var Message $message */
                $message = $update->getMessage();
                try {
                    /** @var User $user */
                    $user = $message->getLeftChatMember();
                    $userName = '@' . $user->getUserName() ?: "Петуч";
                    $text = $userName . ', предатель...';
                    $client->sendMessage($message->getChat()->getId(), $text);
                } catch (Throwable $exception) {
                    $client->sendMessage($message->getChat()->getId(), $exception->getMessage());
                }
            },
            function (Update $update) {
                return !(!$update->getMessage() || !$update->getMessage()->getLeftChatMember());
            }
        );
    }

    /**
     * @return void
     */
    private function setCallBackEvents(): void
    {
        $client = $this->client;
        /**
         * callback
         */
        $client->callbackQuery(function (CallbackQuery $call) use ($client) {
            try {
                (new CallbackHandler())->execute($call, $client);
            } catch (Throwable $exception) {
                /** @var Message $mess */
                $mess = $call->getMessage();
                $client->sendMessage($mess->getChat()->getId(), $exception->getMessage());
            }
        });
    }

    private function setCaptionCommandEvents(): void
    {
        $client = $this->client;


        $files = glob('../src/Logic/BotCommands/*.php');
        foreach ($files as $class) {
            $class = '\App\Logic\BotCommands\\' . pathinfo($class, PATHINFO_FILENAME);
            if (!is_a($class, BotCommandInterface::class, true)) {
               continue;
            }
            $instance = new $class();
            $client->on(
                function (Update $update) use ($client, $instance) {
                    try {
                        /** @var Message $message */
                        $message = $update->getMessage();
                        $instance->setArgumentsByText($instance->getCommand(), $message->getCaption());
                        $instance->execute($message, $client);
                    } catch (Throwable $exception) {
                        $client->sendMessage($message->getChat()->getId(), $exception->getMessage());
                    }
                },
                function (Update $update) use ($instance) {
                    $message = $update->getMessage();
                    if (!$message) {
                        return false;
                    }
                    $text = $message->getCaption();
                    if (empty($text)) {
                        return false;
                    }

                    preg_match(Client::REGEXP, $text, $matches);

                    return !empty($matches) && $matches[1] === $instance->getCommand();
                }
            );

        }

    }
}
