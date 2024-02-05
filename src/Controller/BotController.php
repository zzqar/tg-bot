<?php
/**
 * @DentalPRO TGBOT
 *
 * @see api reference
 * https://github.com/TelegramBot/Api
 */

namespace App\Controller;

use App\Exceptions\BadRequestApiException;
use App\Exceptions\UnauthorizedApiException;
use App\Helpers\ClientHelper;
use CURLFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TelegramBot\Api\Client;

use Throwable;

class BotController extends AbstractController
{

    /**
     * @Route("/bot/send/{chatID}", name="app_bot_send")
     * @throws BadRequestApiException
     * @throws UnauthorizedApiException
     */
    public function send(
        string  $chatID,
        Request $request,
        Client  $client
    ): JsonResponse
    {
        $text = $request->get('text');


        if (empty($text)) {
            throw new BadRequestApiException('Текст сообщения ?');
        }


        $client->sendMessage(
            $chatID,
            $text
        );

        return $this->json([
            'status' => true
        ]);
    }


    /**
     * @Route("/bot/file/{chatID}", name="app_bot_send_file")
     * @throws BadRequestApiException
     * @throws UnauthorizedApiException
     */
    public function sendFile(
        string  $chatID,
        Request $request,
        Client  $client
    ): JsonResponse
    {
        /**
         * @var UploadedFile $file
         */
        $file = $request->files->get('file');

        $client->sendDocument(
            $chatID,
            new CURLFile(
                $file->getPathname(),
                $file->getMimeType(),
                $file->getClientOriginalName()
            )
        );

        return $this->json([
            'status' => true,
        ]);
    }

    /**
     * @Route("/bot/incoming", name="app_bot_incoming_hook")
     */
    public function incoming(
        Client $client
    ): JsonResponse
    {
        try {
            new ClientHelper($client);
        } catch (Throwable $exception) {
            return $this->json([
                'error' => $exception->getMessage(),
            ]);
        }

        return $this->json([
            'status' => true,
        ]);
    }
}
