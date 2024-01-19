<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use GuzzleHttp\Exception\GuzzleException;
use MathieuViossat\Util\ArrayToTextTable;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use Throwable;

#[TypeCommand(TypeCommand::INFO)]
class AirBotCommand extends BotCommandInterface
{
    protected const PARAM = [
        'CO' => 'Углекислый газ',
        'NO2' => 'Диоксид азота',
        'O3' => 'Озон',
        'SO2' => 'Диоксид серы',
        'PM2.5' => 'Мелкие частицы',
        'PM10' => 'Крупные частицы',
        'overall_aqi' => 'Качество воз.',
        'cloud_pct' => 'Облачность',
        'temp' => 'Температура',
        'feels_like' => 'Ощущается как',
        'humidity' => 'Влажность',
        'min_temp' => 'Мин t',
        'max_temp' => 'Макс t',
        'wind_speed' => 'Скорость ветра',
        'wind_degrees' => 'Направ. ветра',
        'sunrise' => 'Восхода',
        'sunset' => 'Заката'
    ];

    protected const TIMESTAMP_PARAM = [
        'sunrise', 'sunset'
    ];
    protected const CASH_PREFIX = 'air_';

    public function getCommand(): string
    {
        return 'air';
    }

    public function getDescription(): string
    {
        return 'погода';
    }

    public function execute(Message $message, Client $client): void
    {
        $city = $this->getText();
        $username = $message->getFrom()->getUsername();
        if ($city === null) {
            $city = $this->getCityByUser($username) ?? 'ulyanovsk';
        } else {
            $this->setCityByUser($username, $city);
        }

        try {
            $data = $this->getAirData($city);
        }catch (Throwable $e){
            $client->sendMessage(
                $message->getChat()->getId(),
                "Хуевый город не найден",
            );
            return;
        }

        $renderData = [];
        foreach ($data as $key => $val) {
            $renderData[] = [
                'Параметр' => self::PARAM[$key] ?? $key,
                'Значение' => $this->handlerValue($key, $val),
            ];
        }
        $renderer = new ArrayToTextTable($renderData);
        $text = $this->textToBoldHTML(ucfirst($city))
            . ' '
            . $this->textToCodeHTML($renderer->getTable());

        $client->sendMessage(
            $message->getChat()->getId(),
            $text,
            'html'
        );
    }

    protected function handlerValue(string $key, $val)
    {
        $val = is_array($val) ? $val['concentration'] : $val;
        if (in_array($key, self::TIMESTAMP_PARAM)) {
            $val = date('H:i', $val);
        }
        return $val;
    }

    private function setCityByUser(string $userName, string $cityName): void
    {
        $cache = new FilesystemAdapter();
        /** @var CacheItem $cacheItem */
        $cacheItem = $cache->getItem(self::CASH_PREFIX . $userName);
        $cacheItem->set($cityName);
        $cache->save($cacheItem);
    }

    private function getCityByUser(string $userName): ?string
    {
        $cache = new FilesystemAdapter();
        /** @var CacheItem $cacheItem */
        $cacheItem = $cache->getItem(self::CASH_PREFIX . $userName);
        return $cacheItem->get();
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    protected function getAirData(string $city): array
    {
        $w = (new \GuzzleHttp\Client())
            ->get('https://api.api-ninjas.com/v1/weather', [
                'query' => [
                    'city' => $city,
                ],
                'headers' => [
                    'X-Api-Key' => 'u6Gr+Z0L1mCAkYDtovCxQQ==HOL2qEZ6hDvb2cVG',
                ],
            ])
            ->getBody()
            ->getContents();
        $w = json_decode($w, true, 512, JSON_THROW_ON_ERROR);

        $air = (new \GuzzleHttp\Client())
            ->get('https://api.api-ninjas.com/v1/airquality', [
                'query' => [
                    'city' => $city,
                ],
                'headers' => [
                    'X-Api-Key' => 'u6Gr+Z0L1mCAkYDtovCxQQ==HOL2qEZ6hDvb2cVG',
                ],
            ])
            ->getBody()
            ->getContents();

        $data = json_decode($air, true, 512, JSON_THROW_ON_ERROR);
        $data += $w;
        return $data;
    }

}
