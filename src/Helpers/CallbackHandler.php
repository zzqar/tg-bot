<?php

namespace App\Helpers;

use App\Attribute\Command;
use App\Interfaces\CallbackBotCommandInterface;
use Generator;
use Psr\Cache\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\CallbackQuery;

class CallbackHandler
{
    protected array $commandRouts = [];

    protected string $actionCommand;
    protected FilesystemAdapter $cache;

    /**
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->cache = new FilesystemAdapter;
        $this->setRouts();
    }

    /**
     * @throws ReflectionException
     */
    protected function setRouts(): void
    {
        $files = glob('../src/Logic/CallbackCommands/*.php');

        foreach ($files as $classFile) {
            $class = '\App\Logic\CallbackCommands\\' . pathinfo($classFile, PATHINFO_FILENAME);
            if (is_a($class, CallbackBotCommandInterface::class, true)) {
                $commands = $this->getCommandsFromClass($class);
                $this->commandRouts += iterator_to_array($commands);
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    private function getCommandsFromClass(string $class): Generator
    {
        $methods = (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            foreach ($method->getAttributes(Command::class) as $attr) {
                /** @var Command $attrInstance */
                $attrInstance = $attr->newInstance();

                yield $attrInstance->getCommand() => [
                    'callbackController' => $class,
                    'action' => $method->getName(),
                    'uniq' => $attrInstance->isUniqRequestMode(),
                ];
            }
        }
    }

    /**
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    public function execute(CallbackQuery $call, Client $client): void
    {
        $command = $this->getActionByData($call->getData());
        /** @var CacheItem $item */
        $item = $this->cache->getItem('callback_last_request');
        $itemData = [
            $call->getFrom()->getId(),
            $call->getData()
        ];
        if ($command['uniq'] && !array_diff($itemData, $item->get() ?? [])) {
            return;
        }
        $item->set($itemData);
        $this->cache->save($item);

        /** @var CallbackBotCommandInterface $instance */
        $instance = new $command['callbackController']($client, $call, $this->actionCommand);
        $instance->{$command['action']}();
    }

    /**
     * @throws \Exception
     */
    protected function getActionByData(?string $text): ?array
    {
        if (empty($text)) {
            throw new Exception('Not Found text');
        }
        preg_match(Client::REGEXP, $text, $matches);

        if (empty($matches) || !array_key_exists($matches[1], $this->commandRouts)) {
            throw new Exception('Not found' . '/' . $text . ' | ' . $matches[1] ?? 'match not found');
        }

        $this->actionCommand = $matches[1];

        return $this->commandRouts[$matches[1]];
    }
}
