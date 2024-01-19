<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use ReflectionClass;
use ReflectionException;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::INFO)]
class HelpBotCommand extends BotCommandInterface
{

    public function getCommand(): string
    {
        return 'list';
    }

    public function getDescription(): string
    {
        return 'список команд';
    }

    /**
     * @throws ReflectionException
     */
    public function execute(Message $message, Client $client): void
    {
        $files = glob('../src/Logic/BotCommands/*.php');

        $commands = $text = [];
        foreach ($files as $class) {
            $class = '\App\Logic\BotCommands\\' . pathinfo($class, PATHINFO_FILENAME);
            if (is_a($class, BotCommandInterface::class, true)) {
                $instance = new $class();
                if ($instance->isHide()) {
                    continue;
                }
                $reflectionClass = new ReflectionClass($class);
                $atrs = $reflectionClass->getAttributes(TypeCommand::class);
                if (count($atrs) === 0) {
                    $commands[TypeCommand::ALL][] = '* /' . $instance->getCommand() . ' - ' . $instance->getDescription();
                } else {
                    foreach ($atrs as $atr) {
                        /** @var TypeCommand $atrInstance */
                        $atrInstance = $atr->newInstance();
                        $commands[$atrInstance->getType()][] = '* /' . $instance->getCommand() . ' - ' . $instance->getDescription();
                    }

                }
            }
        }
        foreach ($commands as $type => $command) {
            $text[] = "<strong>" . TypeCommand::TYPES[$type] . "</strong>";
            $text = array_merge($text, $command);
        }
        $client->sendMessage(
            $message->getChat()->getId(),
            implode("\n", $text),
            'html'
        );
    }
}
