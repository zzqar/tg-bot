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

    public function getAnother(): ?BoardValue
    {
        foreach (self::cases() as $enum){
            if (!in_array($enum, [$this, self::null], true)){
                return $enum;
            }
        }
        return null;
    }


}

