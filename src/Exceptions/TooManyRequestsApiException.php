<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\ApiExceptionInterface;

class TooManyRequestsApiException extends \Exception implements ApiExceptionInterface
{
    protected $code = 429;

    protected $url = '';

    protected $hash = '';

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return TooManyRequestsApiException
     */
    public function setHash(string $hash): TooManyRequestsApiException
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return TooManyRequestsApiException
     */
    public function setUrl(string $url): TooManyRequestsApiException
    {
        $this->url = $url;
        return $this;
    }


}