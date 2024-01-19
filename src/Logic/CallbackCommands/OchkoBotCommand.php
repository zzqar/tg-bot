<?php

namespace App\Logic\CallbackCommands;

use App\Attribute\Command;
use App\Interfaces\CallbackBotCommandInterface;
use App\Logic\OchkoGame;
use Psr\Cache\InvalidArgumentException;


class OchkoBotCommand extends CallbackBotCommandInterface
{
    /**
     * @throws InvalidArgumentException
     */
    #[Command('omore')]
    public function registerAction(): void
    {
        (new OchkoGame($this->client, $this->getMessage()))->more($this->callback);
    }

    #[Command('odone')]
    public function restart(): void
    {
        (new OchkoGame($this->client, $this->getMessage()))->more($this->callback, false);
    }

    #[Command('stat')]
    public function stat(): void
    {
        (new OchkoGame($this->client, $this->getMessage()))->stat($this->callback);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Command('back')]
    public function back(): void
    {
        (new OchkoGame($this->client, $this->getMessage()))
            ->runGame($this->callback->getFrom(), $this->getMessage()->getMessageId());

    }
}
