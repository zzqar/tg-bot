<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\ApiExceptionInterface;

class NotFoundApiException extends \Exception implements ApiExceptionInterface
{
    protected $code = 404;
}