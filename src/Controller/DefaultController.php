<?php

namespace App\Controller;

use GuzzleHttp\Client;
use Orhanerday\OpenAi\OpenAi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_default")
     */
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    /**
     * @Route("/status", name="status")
     */
    public function status(): JsonResponse
    {
        $result = (new Client([
            'base_uri' => 'https://api.telegram.org'
        ]))
            ->get("/bot{$_ENV['TG_BOT_SECRET']}/getWebhookInfo")
            ->getBody()
            ->getContents();

        $result = json_decode($result, true);

        return $this->json([
            'service information' => $result['result'],
            'last_error_date' => date('d.m.Y H:i:s', $result['result']['last_error_date'])
        ]);
    }

    /**
     * @Route("/test", name="test")
     */
    public function test(): Response
    {
        $open_ai = new OpenAi($_ENV['OPENAI_KEY']);
        $result = $open_ai->image([
            "prompt" => 'nuclear explosion in moscow in ghibly anime style',
            "n" => 4,
            "size" => "1024x1024",
            "response_format" => "url",
        ]);

        $result = json_decode($result, true);
        if ($result['error'] ?? null) {
            throw new \Exception($result['error']['message']);
        }

        return $this->json($result);
    }


}
