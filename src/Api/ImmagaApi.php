<?php

namespace App\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ImmagaApi
{
    protected Client $client;
    protected string $url = 'https://api.imagga.com/v2/categories/personal_photos';

    protected string $lang;
    private string $imageUrl;

    public function __construct(string $url)
    {
        $this->client = new Client();
        $this->imageUrl = $url;
    }

    public function getResponse()
    {
        $api_credentials = [
            'key' => 'acc_a6b65391746c934',
            'secret' => 'b3bfc4228174b02718c1ec5dbff5eb9e'
        ];

        $client = new Client();

        try {
            $response = $client->request(
                'GET',
                $this->url,
                [
                    'auth' => [$api_credentials['key'], $api_credentials['secret']],
                    'query' => [
                        'image_url' => $this->imageUrl,
                        'language' => 'ru',
                        'limit' => 10,
                    ],
                ]
            );
            $data = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            return $data['result']['categories'];
        } catch (\Throwable $exception) {
            return ['msg' => $exception->getMessage()];
        }

    }
}

