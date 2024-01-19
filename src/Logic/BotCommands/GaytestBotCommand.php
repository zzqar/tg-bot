<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

#[TypeCommand(TypeCommand::TEST)]
class GaytestBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'gaytest';
    }

    public function getDescription(): string
    {
        return "ЛГБТ тест \xF0\x9F\x8C\x88";
    }

    public function execute(Message $message, Client $client): void
    {
        $cache = new FilesystemAdapter();
        $userName = $message->getFrom()->getUsername();
        $gayU = $cache->get('gaytest_u_' . $userName, function (ItemInterface $item): string {
            $item->expiresAfter(3600);
            return random_int(0, 100);
        });


        $gayC = $cache->get('gaytest_c_' . $userName, function (ItemInterface $item): string {
            $item->expiresAfter(3600);
            return random_int(0, 100);
        });

        $text = [
            "@{$userName}"
        ];

        if ($gayC > 50) {
            $text[] = 'пассивный гей';
        } else {
            $text[] = 'активный гей';
        }

        $text[] = "с вероятностью {$gayU}%,";

        if ($gayU < 70) {
            $text[] = 'Ну хз хз, проверять надо...';
        } else {

            try {
                $usersSaved = file_get_contents('users.json');

                if (empty($usersSaved)) {
                    $users = [
                        '@noanaoki' => '@noanaoki',
                    ];
                } else {
                    $users = json_decode($usersSaved, true);
                }

                $users['@' . $message->getFrom()->getUsername()] = '@' . $message->getFrom()->getUsername();
                touch('users.json');
                file_put_contents('users.json', json_encode($users));

                $userTarget = random_int(0, count($users) - 1);
                $users = array_values($users);

                $hello = array(
                    'мамой клянус',
                    'инфа сотка',
                    'на заборе написано',
                    'птичка на хвосте принесла',
                    'один уй нашептал',
                    'видел как сосал',
                    'замечен с ' . $users[$userTarget],
                    'теребонькал у ' . $users[$userTarget],
                    'рассказал ' . $users[$userTarget],
                    'с раздолбленым очком от ' . $users[$userTarget],
                    'подглядывал ' . $users[$userTarget],
                    'глубокая глотка по заявлению ' . $users[$userTarget],

                );

            } catch (\Throwable $exception) {
                $client->sendMessage($message->getChat()->getId(), $exception->getMessage());
                return;
            }
            $phraseID = random_int(0, count($hello) - 1);
            $text[] = $hello[$phraseID] ?? 'и че';
        }


        $client->sendMessage($message->getChat()->getId(), implode(' ', $text));
    }


}
