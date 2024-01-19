<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\ApiExceptionInterface;

class UnauthorizedApiException extends \Exception implements ApiExceptionInterface
{
    protected $code = 401;
}