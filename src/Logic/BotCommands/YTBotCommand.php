<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia;
use TelegramBot\Api\Types\InputMedia\InputMediaVideo;
use TelegramBot\Api\Types\Message;
use YouTube\Exception\YouTubeException;
use YouTube\Models\StreamFormat;
use YouTube\YouTubeDownloader;

#[TypeCommand(TypeCommand::INFO)]
class YTBotCommand extends BotCommandInterface
{
    public function getCommand(): string
    {
        return 'yt';
    }

    public function getDescription(): string
    {
        return 'скачать с YouTube';
    }

    /**
     * @param StreamFormat[] $formats
     * @return InlineKeyboardMarkup
     */
    private function keyboard(array $formats): InlineKeyboardMarkup
    {
        $keyboard = [];

        foreach ($formats as $format) {
            $keyboard[] = [
                ['text' => $format->quality, 'url' => $format->url]
            ];
        }


        return new InlineKeyboardMarkup($keyboard);
    }

    public function execute(Message $message, Client $client): void
    {
        $link = $this->getText();

        if (empty($link)) {
            $client->sendMessage($message->getChat()->getId(), 'Юзай /yt https://www.youtube.com/watch?v=dQw4w9WgXcQ');
            return;
        }


        $youtube = new YouTubeDownloader();

        try {
            $downloadOptions = $youtube->getDownloadLinks($link);

            if ($downloadOptions->getAllFormats()) {
                $formats = $downloadOptions->getCombinedFormats();

                $client->deleteMessage($message->getChat()->getId(), $message->getMessageId());

                $client->sendMessage(
                    $message->getChat()->getId(),
                    'Ссылки для скачивания: ' . $link,
                    null,
                    false,
                    null,
                    $this->keyboard($formats)
                );

                foreach ($formats as $format) {
                    if ($format->quality != 'hd720') {
                        continue;
                    }
                    $media = new ArrayOfInputMedia();
                    $media->addItem(
                        new InputMediaVideo($format->url)
                    );
                    $client->sendMediaGroup($message->getChat()->getId(), $media);
                    break;
                }


            } else {
                $client->sendMessage($message->getChat()->getId(), 'Ссылок не найдено');
            }

        } catch (YouTubeException $e) {
            $client->sendMessage($message->getChat()->getId(), $e->getMessage());
        }


    }


}
