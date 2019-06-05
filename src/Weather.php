<?php
/**
 * This file is part of the luoweikingjj/weather.
 *
 * (c) luoweikingjj <i@luowe.top>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Luoweikingjj\Weather;

use GuzzleHttp\Client;
use Luoweikingjj\Weather\Exceptions\HttpException;
use Luoweikingjj\Weather\Exceptions\InvalidArgumentException;


/**
 * Class Weather
 * @package Luoweikingjj\Weather
 */
class Weather
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var array
     */
    private $guzzleOptions = [];

    /**
     * Weather constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * @return Client
     */
    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    /**
     * @param array $options
     */
    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     * @param $city
     * @param string $type
     * @param string $format
     * @return mixed|string
     * @throws HttpException
     * @throws InvalidArgumentException
     */
    public function getWeather($city, string $type = 'live', string $format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        $types = [
            'live' => 'base',
            'forecast' => 'all',
        ];

        // 1. 对 `$format` 与 `$type` 参数进行检查，不在范围内的抛出异常。
        if (!\in_array(\strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: '.$format);
        }

        if (!\array_key_exists(\strtolower($type), $types)) {
            throw new InvalidArgumentException('Invalid type value(live/forecast): '.$type);
        }

        // 2. 封装 query 参数，并对空值进行过滤。
        $query = array_filter(
            [
                'key' => $this->key,
                'city' => $city,
                'output' => \strtolower($format),
                'extensions' => \strtolower($types[$type]),
            ]
        );

        try {
            // 3. 调用 getHttpClient 获取实例，并调用该实例的 `get` 方法，
            // 传递参数为两个：$url、['query' => $query]，
            $response = $this->getHttpClient()->get(
                $url,
                [
                    'query' => $query,
                ]
            )->getBody()->getContents();

            // 4. 返回值根据 $format 返回不同的格式，
            // 当 $format 为 json 时，返回数组格式，否则为 xml。
            return 'json' === $format ? \json_decode($response, true) : $response;
        } catch (\Exception $e) {
            // 5. 当调用出现异常时捕获并抛出，消息为捕获到的异常消息，
            // 并将调用异常作为 $previousException 传入。
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param $city
     * @param string $format
     * @return mixed|string
     * @throws HttpException
     * @throws InvalidArgumentException
     */
    public function getLiveWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'live', $format);
    }

    /**
     * @param $city
     * @param string $format
     * @return mixed|string
     * @throws HttpException
     * @throws InvalidArgumentException
     */
    public function getForecastWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'forecast', $format);
    }
}