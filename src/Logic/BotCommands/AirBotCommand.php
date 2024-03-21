<?php

namespace App\Logic\BotCommands;

use App\Attribute\TypeCommand;
use App\Interfaces\BotCommandInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use Throwable;

#[TypeCommand(TypeCommand::INFO)]
class AirBotCommand extends BotCommandInterface
{
    protected const TIMESTAMP_PARAM = [
        'sunrise', 'sunset'
    ];
    protected const TEMP_PARAM = [
        'temp', 'feels_like', 'min_temp', 'max_temp'
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
        $username = $message->getFrom()?->getUsername();
        if ($city === null) {
            $city = $this->getCityByUser($username) ?? 'ulyanovsk';
        } else {
            $this->setCityByUser($username, $city);
        }

        try {
            $data = $this->getAirData($city);
        } catch (Throwable) {
            $client->sendMessage(
                $message->getChat()->getId(),
                "Хуевый город не найден",
            );
            return;
        }

        $text = [
            $this->textToBoldHTML(ucfirst($city)) . "\n",

            '🌡 ' . $this->textToBoldHTML('Температура: ')
            . $this->handlerValue('temp', $data['temp'])
            . ' (ощущается как '
            . $this->handlerValue('feels_like', $data['feels_like']) . ')',

            ' - ' . $this->textToBoldHTML('min: ')
            . $this->handlerValue('min_temp', $data['min_temp'])
            ,

            ' - ' . $this->textToBoldHTML('max: ')
            . $this->handlerValue('max_temp', $data['max_temp']),


            '💧 ' . $this->textToBoldHTML('Влажность: ')
            . $this->handlerValue('humidity', $data['humidity']) . ' %',

            ' 🌬 ' . $this->textToBoldHTML('Ветер: ')
            . $this->handlerValue('wind_speed', $data['wind_speed']) . ' м/с, '
            . $this->etWindDirection($this->handlerValue('wind_degrees', $data['wind_degrees'])),

            '☀️ ' . $this->textToBoldHTML('Солнце: ')
            . 'вос. ' . $this->handlerValue('sunrise', $data['sunrise'])
            . ' зак. ' . $this->handlerValue('sunset', $data['sunset']),

            '☁️ ' . $this->textToBoldHTML('Облачность: ')
            . $this->handlerValue('cloud_pct', $data['cloud_pct']) . ' %' . "\n",
            '',

            '😷 ' . $this->textToBoldHTML('Качество воздуха: ') . "({$data['overall_aqi']})\n - "
            . $this->handlerValue('overall_aqi', $data['overall_aqi']),

            '🌡 ' . $this->textToBoldHTML('Углекислый газ: ')
            . $this->handlerValue('CO', $data['CO']) . ' ppm',

            '🌡 ' . $this->textToBoldHTML('Диоксид азота: ')
            . $this->handlerValue('NO2', $data['NO2']) . ' ppb',

            '🌡 ' . $this->textToBoldHTML('Диоксид серы: ')
            . $this->handlerValue('SO2', $data['SO2']) . ' ppb',

            '🌡 ' . $this->textToBoldHTML('Озон: ')
            . $this->handlerValue('O3', $data['O3']) . ' ppb',

            '🌡 ' . $this->textToBoldHTML('Частицы: ')
            . $this->handlerValue('PM2.5', $data['PM2.5'])
            . '-' . $this->handlerValue('PM10', $data['PM10']) . ' µg/m³'
        ];

        $client->sendMessage(
            $message->getChat()->getId(),
            implode("\n", $text),
            'html'
        );
    }

    protected function handlerValue(string $key, $val)
    {
        $val = is_array($val) ? $val['concentration'] : $val;
        if (in_array($key, self::TIMESTAMP_PARAM)) {
            $val = date('H:i', $val);
        }
        if (in_array($key, self::TEMP_PARAM)) {
            $val .= '°C';
        }
        if ($key === 'overall_aqi') {
            $val = $this->getAirQuality($val);
        }
        return $val;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setCityByUser(string $userName, string $cityName): void
    {
        $cache = new FilesystemAdapter();
        /** @var CacheItem $cacheItem */
        $cacheItem = $cache->getItem(self::CASH_PREFIX . $userName);
        $cacheItem->set($cityName);
        $cache->save($cacheItem);
    }

    /**
     * @throws InvalidArgumentException
     */
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
    private function getAirData(string $city): array
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

    private function etWindDirection($degrees): string
    {
        // Массив с диапазонами градусов и соответствующими направлениями ветра
        $directions = [
            ["low" => 0, "high" => 22.5, "direction" => "⬆ Северный"],
            ["low" => 22.5, "high" => 67.5, "direction" => "↗ Северо-восточный"],
            ["low" => 67.5, "high" => 112.5, "direction" => "➡ Восточный"],
            ["low" => 112.5, "high" => 157.5, "direction" => "↘ Юго-восточный"],
            ["low" => 157.5, "high" => 202.5, "direction" => "⬇ Южный"],
            ["low" => 202.5, "high" => 247.5, "direction" => "↙ Юго-западный"],
            ["low" => 247.5, "high" => 292.5, "direction" => "⬅ Западный"],
            ["low" => 292.5, "high" => 337.5, "direction" => "↖ Северо-западный"],
            ["low" => 337.5, "high" => 360, "direction" => "⬆ Северный"]
        ];

        // Перебираем массив с диапазонами и находим подходящий для градусов
        foreach ($directions as $dir) {
            if ($degrees >= $dir["low"] && $degrees <= $dir["high"]) {
                // Возвращаем направление ветра
                return $dir["direction"];
            }
        }
        return '';
    }

    /**
     * Функция для определения качества воздуха по AQI
     *
     * @param $aqi
     * @return string
     */
    private function getAirQuality($aqi): string
    {
        // Массив с диапазонами AQI и соответствующими уровнями качества воздуха
        $quality_levels = [
            ["aqi_low" => 0, "aqi_high" => 50, "quality" => "Хорошее"],
            ["aqi_low" => 51, "aqi_high" => 100, "quality" => "Удовлетворительное"],
            ["aqi_low" => 101, "aqi_high" => 150, "quality" => "Нездоровое для чувствительных групп"],
            ["aqi_low" => 151, "aqi_high" => 200, "quality" => "Нездоровое"],
            ["aqi_low" => 201, "aqi_high" => 300, "quality" => "Очень нездоровое"],
            ["aqi_low" => 301, "aqi_high" => 400, "quality" => "Опасное"],
            ["aqi_low" => 401, "aqi_high" => 500, "quality" => "Чрезвычайно опасное"]
        ];

        // Перебираем массив с диапазонами и находим подходящий для AQI
        foreach ($quality_levels as $level) {
            if ($aqi >= $level["aqi_low"] && $aqi <= $level["aqi_high"]) {
                // Возвращаем уровень качества воздуха
                return $level["quality"];
            }
        }
        return '';
    }


}
