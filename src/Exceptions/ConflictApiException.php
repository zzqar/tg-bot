<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\ApiExceptionInterface;

class ConflictApiException extends \Exception implements ApiExceptionInterface
{
    protected $code = 409;
}