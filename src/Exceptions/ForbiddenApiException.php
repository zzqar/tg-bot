<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\ApiExceptionInterface;

class ForbiddenApiException extends \Exception implements ApiExceptionInterface
{
    protected $code = 403;
}