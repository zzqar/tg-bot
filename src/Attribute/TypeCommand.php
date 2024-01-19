<?php

namespace App\Attribute;


use Attribute;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS)]
class TypeCommand
{
    protected int $type;
    protected string $description;

    public const GAME = 4;
    public const INFO = 1;
    public const MEME = 2;
    public const ALL = 0;
    public const TEST = 3;


    public const TYPES = [
        self::ALL => "Общие",
        self::INFO => "Полезные ?",
        self::MEME => "Мемы",
        self::TEST => "Тесты",
        self::GAME => "Game"
    ];

    public function __construct(int $type)
    {
        $this->type = $type;

    }



    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


}
