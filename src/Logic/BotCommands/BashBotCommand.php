<?php

namespace App\Logic\BotCommands;

use App\Interfaces\BotCommandInterface;
use PhpQuery\PhpQuery;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use function App\Controller\get_inner_html;

class BashBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'bash';
    }

    public function getDescription(): string
    {
        return 'что это ? (not supported)';
    }

    public function isHide(): bool
    {
        return true;
    }
    public function execute(Message $message, Client $client): void
    {
        $meme = (new \GuzzleHttp\Client())
            ->get('http://bashorg.org/random')
            ->getBody()
            ->getContents();

        $pq = (new PhpQuery());
        $pq->load_str($meme);
        $quotes = $pq->query('.quote');
        /**
         * @var \DOMElement $item
         */
        $item = $quotes->item(0);


        $quote = str_replace('<br />', " \n ", $this->get_inner_html($item));
        $client->sendMessage($message->getChat()->getId(), $quote);
    }

    protected function get_inner_html($node)
    {
        $innerHTML = '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }
        return $innerHTML;
    }


}
