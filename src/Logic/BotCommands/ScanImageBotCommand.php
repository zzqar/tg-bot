<?php

namespace App\Logic\BotCommands;

use App\Api\ImmagaApi;
use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\PhotoSize;
use TelegramBot\Api\Types\User;

#[TypeCommand(TypeCommand::INFO)]
class ScanImageBotCommand extends BotCommandInterface
{
    const CASH_PREFIX = 'scan_';

    public function getCommand(): string
    {
        return 'scan';
    }

    public function getDescription(): string
    {
        return 'распознает объекты на фото';
    }

    public function execute(Message $message, Client $client): void
    {
        /** @var User $user */
        $user = $message->getFrom();

        $cashParam = $this->getParamByKey('cash');
        if ($cashParam !== null && $user->getId() === 1442490395) {
            match ($cashParam) {
                'all' => $this->clearAllCash(),
                default => $this->clearByName($cashParam)
            };
            return;
        }

        if ($this->getCountUserRequest($user->getUsername()) >= 10) {
            $client->sendMessage(
                $message->getChat()->getId(),
                "Первышен лимит в 10 запросов в день",
                "html"
            );
            return;
        }

        $url = $this->getImageURL($client, $message);


        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $scan = new ImmagaApi($url);
            $data = $scan->getResponse();
            $text[] = "Я уверен тут изораженно:";
            foreach ($data as $tag) {
                $text[] = "На " . round($tag['confidence'], 2) . '%, что это ' . $tag['name']['ru'];
            }
            $client->sendMessage(
                $message->getChat()->getId(),
                implode("\n", $text),
                "html"
            );
            $this->addRequsetByUser($user->getUsername());
        } else {
            $client->sendMessage(
                $message->getChat()->getId(),
                "Хреновый URL: " . $url ?: 'null',
                "html"
            );
        }
    }

    private function getCountUserRequest(string $userName): int
    {
        $cache = new FilesystemAdapter();
        /** @var CacheItem $cacheItem */
        $cacheItem = $cache->getItem(self::CASH_PREFIX . $userName);
        return (int)$cacheItem->get() ?: 0;

    }

    private function addRequsetByUser(string $userName)
    {
        $cache = new FilesystemAdapter();
        /** @var CacheItem $cacheItem */
        $cacheItem = $cache->getItem(self::CASH_PREFIX . $userName);
        $count = $cacheItem->get() ?: 0;

        if ($count === 0) {
            $cacheItem->expiresAfter(60 * 60 * 24);
        }
        $cacheItem->set($count + 1);
        $cache->save($cacheItem);
    }

    private function clearAllCash()
    {
        $cache = new FilesystemAdapter();
        $cache->clear(self::CASH_PREFIX);
    }


    private function getImageURL(Client $client, Message $message): ?string
    {
        if (($url = $this->getText()) !== null) {
            return $url;
        }

        $photoSize = $message->getPhoto();

        if (is_array($photoSize)) {
            $photoSize = reset($photoSize);
            if ($photoSize instanceof PhotoSize) {
                $file = $client->getFile($photoSize->getFileId());
                return $client->getFileUrl() . '/' . $file->getFilePath();
            }
        }
        return null;
    }

    private function clearByName(string $cashParam)
    {
        $cache = new FilesystemAdapter();
        $cache->delete(self::CASH_PREFIX . $cashParam);
    }
}
