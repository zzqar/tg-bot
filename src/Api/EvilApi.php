<?php

namespace App\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class EvilApi
{
    protected Client $client;
    protected string $url = 'https://evilinsult.com/generate_insult.php?lang={ln}&type=json';

    protected string $lang;

    public function __construct(?string $lang = null)
    {
        $this->client = new Client();
        $this->lang = $lang ?? 'ru';
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function getResponse()
    {
        $evilResponse = ($this->client)
            ->get(str_replace('{ln}', $this->lang, $this->url))
            ->getBody()
            ->getContents();

        $date = json_decode($evilResponse, true, 512, JSON_THROW_ON_ERROR);
        return lcfirst($date['insult']);
    }
}
