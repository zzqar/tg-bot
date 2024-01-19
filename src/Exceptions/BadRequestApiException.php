<?php

namespace App\Exceptions;

use App\Exceptions\Interfaces\ApiExceptionInterface;

class BadRequestApiException extends \Exception implements ApiExceptionInterface
{
    protected $code = 400;

}
