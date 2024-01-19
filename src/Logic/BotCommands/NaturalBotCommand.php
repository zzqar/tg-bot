<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\User;

#[TypeCommand(TypeCommand::TEST)]
class NaturalBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'natural';
    }

    public function getDescription(): string
    {
        return "тест на натурала";
    }

    public function execute(Message $message, Client $client): void
    {
        $cache = new FilesystemAdapter();

        if ($this->getText()) {
            $userName = trim($this->getText(), " \t\n\r\0\x0B@");
            $text[] =  "@{$userName}";
        } else {
            /** @var User $user */
            $user = $message->getFrom();
            $name =  $user->getFirstName() ?: $user->getUsername() ?: 'чел';
            $text[] =  "<a href='tg://user?id={$user->getId()}'>" . $name . "</a>";
            $userName = $user->getUsername();
        }
        $text[] = ' - ';

        if($this->getParamByKey('clear')) {
            $cache->delete('gaytest_u_' . $userName);
        }
        $gayU = $cache->get('gaytest_u_' . $userName, function (ItemInterface $item): string {
            $item->expiresAfter(3600);
            return random_int(0, 100);
        });

        $natural = 100 - $gayU;
        $text[] = match (true) {
            (isset($user) && $user->getIsPremium()) => "c TG-Перимиумом по дефолту педик",
            ($natural > 95) => "убер-натурал",
            ($natural > 70) => "натурал, почти...",
            ($natural > 50) => "натурал, но были эксперименты",
            ($natural > 30) => "пидорковатость присутствует",
            ($natural > 10) => "один раз не пидорас да ? НЕТ!",
            default => "ПИДОР"
        };

        $client->sendMessage($message->getChat()->getId(),
            implode(' ', $text),
            'html'
        );
    }


}
