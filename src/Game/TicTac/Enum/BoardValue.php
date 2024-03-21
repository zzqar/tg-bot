<?php

namespace App\Game\TicTac\Enum;

enum BoardValue: string
{
    case x = '❌';
    case o = '⭕';
    case null = '◻️';

    public static function getByIndex(int $index): BoardValue
    {
        $enums = self::cases();
        return $enums[$index];
    }

    public function getAnother(): BoardValue
    {
        return match ($this) {
            self::x => self::o,
            self::o => self::x,
            default => self::null
        };
    }

}

