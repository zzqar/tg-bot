<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use function App\Controller\huify;

class HuiBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'hui';
    }

    public function getDescription(): string
    {
        return "\xF0\x9F\x8C\x88\xF0\x9F\x8C\x88\xF0\x9F\x8C\x88";
    }

    public function execute(Message $message, Client $client): void
    {
        $text = trim(str_replace('/hui', '', $message->getText()));

        if (mb_strlen($text) > 200) {
            $client->sendMessage($message->getChat()->getId(), 'Иди и сам переводи, я чайник');
            return;
        }


        $messageTxt = [];
        $words = explode(' ', $text);
        foreach ($words as $word) {
            if (mb_strlen($word) <= 3) {
                $messageTxt[] = $word;
            } else {
                $messageTxt[] = $this->huify($word);
            }
        }

        $client->sendMessage($message->getChat()->getId(), implode(' ', $messageTxt));
    }

    protected function huify($word): string
    {
        $input = $word;
        $word = mb_strtolower(trim($word));
        $vowels = 'аеёиоуыэюя';
        $rules = [
            'а' => 'я',
            'о' => 'е',
            'у' => 'ю',
            'ы' => 'и',
            'э' => 'е',
        ];

        while ($word != '') {
            $letter = mb_substr($word, 0, 1);
            if (mb_strpos($vowels, $letter) !== false) {
                if (array_key_exists($letter, $rules)) {
                    $word = $rules[$letter] . mb_substr($word, 1);
                }
                break;
            } else {
                $word = mb_substr($word, 1);
            }
        }

        return empty($word) ? 'Хуй' : $input . '-Ху' . $word;
    }


}
