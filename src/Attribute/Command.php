<?php

namespace App\Attribute;


use Attribute;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Command
{
    protected string $command;
    protected string $description;


    public function __construct(string $command, string $description = '')
    {
        $this->command = $command;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }


}
