<?php

namespace App\Helpers;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class GameResponse implements Response
{

    protected string $text;
    protected ?InlineKeyboardMarkup $inlineKeyboard;

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return InlineKeyboardMarkup|null
     */
    public function getInlineKeyboard(): ?InlineKeyboardMarkup
    {
        return $this->inlineKeyboard;
    }

    public function __construct(string $text, ?InlineKeyboardMarkup $inlineKeyboard = null)
    {
        $this->text = $text;
        $this->inlineKeyboard = $inlineKeyboard ?? new InlineKeyboardMarkup();

    }


}
