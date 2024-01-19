<?php

namespace App\Trait;

/**
 * парсит команду, параметры и текст
 */
trait Bot
{
    use HTML;
    private array $param = [];
    private ?string $text;

    /**
     * Получает текст из сообщения, без команды и параметров
     *
     * @param string|null $default
     * @return string|null
     */
    public function getText(?string $default = null): ?string
    {
        return $this->text ?: $default;
    }


    /**
     * Получает параметры из сообщения записанные в виде: -paramKey=paramValue
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->param;
    }

    public function getParamByKey(string $key, ?string $default = null): ?string
    {
        return $this->param[$key] ?? $default;
    }

    public function setArgumentsByText(string $command, string $text): void
    {
        $text = trim(str_replace('/' . $command, '', $text));

        if (preg_match_all('/-([a-zA-Z]+)=(\S+)(?:\s+|$)/', $text, $matches, PREG_PATTERN_ORDER)) {
            $params = [];

            for ($i = 0, $iMax = count($matches[1]); $i < $iMax; $i++) {
                $paramName = $matches[1][$i];
                $paramValue = $matches[2][$i] ?? null;
                $params[$paramName] = $paramValue;
                // Удалить параметры из текста
                $text = str_replace($matches[0][$i], '', $text);
            }
            $text = trim($text);
            $this->param = $params;
        }
        $this->text = $text;
    }
}
