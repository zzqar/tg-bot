<?php

namespace App\Api;

use GuzzleHttp\Exception\GuzzleException;

class HoroApi
{
    public const  ZNAKI = [
        'aries' => 'овен',
        'taurus' => 'телец',
        'gemini' => 'близнецы',
        'cancer' => 'рак',
        'leo' => 'лев',
        'virgo' => 'дева',
        'libra' => 'весы',
        'scorpio' => 'скорпион',
        'sagittarius' => 'стрелец',
        'capricorn' => 'козерог',
        'aquarius' => 'водолей',
        'pisces' => 'рыбы',
    ];

    /**
     * TODO Надо в кэш засунуть
     *
     * @param $key
     * @return string
     * @throws GuzzleException
     */
    static function getByKey($key): string
    {
        $xml = (new \GuzzleHttp\Client())
            ->get('https://ignio.com/r/export/utf/xml/daily/com.xml')
            ->getBody()
            ->getContents();

        $xml = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        $array = json_decode(json_encode($xml), true);

        return $array[$key]['today'];
    }

}
