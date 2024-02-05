<?php

namespace App\Api;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class GdeposylkaApi
{
    protected string $url = 'https://gdeposylka.ru/api/v4/tracker/detect/{tracker_id}';
    protected string $token = '3bb8aa87ecdadbef2d680843fae783123f61cc61e806fbf48452963402183ab03f95a3e3a4554a19';
/**
 * TODO мне не подтвердили токен (
 */
    public function getAllInfoByTrackingId($tracker)
    {
        $response = (new Client)->request(
            'GET',
            str_replace('{tracker_id}', $tracker, $this->url),
            [
                RequestOptions::HEADERS => [
                    'X-Authorization-Token' => $this->token,
                ]
            ]
        )->getBody()->getContents();
        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}
