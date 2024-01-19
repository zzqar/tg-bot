<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::TEST)]
class BebraBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'bebra';
    }

    public function getDescription(): string
    {
        return "линейка \xF0\x9F\x8D\x86";
    }

    public function execute(Message $message, Client $client): void
    {
        $userName = $message->getFrom()->getUsername();
        $cache = new FilesystemAdapter();

        $rand = $cache->get('bebera' . $userName, function (ItemInterface $item): string {
            $item->expiresAfter(3600);
            return random_int(0, 30);
        });

        if ($userName === 'noanaoki') {
            $rand = abs($rand - 15);
        }

        if ($rand < 15) {
            $client->sendMessage($message->getChat()->getId(), "У @{$userName} писюн {$rand} см, инфа сотка." . $this->getRandomPhrase('bebra'));
        } elseif ($rand == 15) {
            $client->sendMessage($message->getChat()->getId(), '15 см - Идеал для Насти Ивлеевой');
        } else {
            $client->sendMessage($message->getChat()->getId(), "У @{$userName} писюн {$rand} см, инфа сотка.");
        }
    }


}
