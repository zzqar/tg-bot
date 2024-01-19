<?php

namespace App\Helpers;

class GameAlert implements Response
{
    public function __construct(protected string $text){
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }


}
