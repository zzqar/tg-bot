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
    protected string $uniq;


    public function __construct(string $command, bool $uniq = false, string $description = '')
    {
        $this->command = $command;
        $this->description = $description;
        $this->uniq = $uniq;
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

    public function isUniqRequestMode(): bool
    {
        return $this->uniq;
    }



}
