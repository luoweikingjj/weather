<?php

/*
 * This file is part of the luoweikingjj/weather.
 *
 * (c) luoweikingjj <i@luowe.top>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Luoweikingjj\Weather\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Luoweikingjj\Weather\Exceptions\HttpException;
use Luoweikingjj\Weather\Exceptions\InvalidArgumentException;
use Luoweikingjj\Weather\Weather;
use Mockery\Matcher\AnyArgs;
use PHPUnit\Framework\TestCase;

/**
 * Class WeatherTest.
 */
class WeatherTest extends TestCase
{
    /**
     * @throws HttpException
     * @throws InvalidArgumentException
     */
    public function testGetWeatherWithInvalidType()
    {
        $w = new Weather('mock-key');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type value(live/forecast): foo');

        $w->getWeather('深圳', 'foo');

        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }

    /**
     * @throws HttpException
     * @throws InvalidArgumentException
     */
    public function testGetWeatherWithInvalidFormat()
    {
        $w = new Weather('mock-key');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid response format: array');

        $w->getWeather('深圳', 'base', 'array');

        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }

    public function testGetWeather()
    {
        // json
        $response = new Response(200, [], '{"success": true}');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get(
            'https://restapi.amap.com/v3/weather/weatherInfo',
            [
                'query' => [
                    'key' => 'mock-key',
                    'city' => '深圳',
                    'output' => 'json',
                    'extensions' => 'base',
                ],
            ]
        )->andReturn($response);

        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(['success' => true], $w->getLiveWeather('深圳'));

        // xml
        $response = new Response(200, [], '<hello>content</hello>');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get(
            'https://restapi.amap.com/v3/weather/weatherInfo',
            [
                'query' => [
                    'key' => 'mock-key',
                    'city' => '深圳',
                    'extensions' => 'all',
                    'output' => 'xml',
                ],
            ]
        )->andReturn($response);

        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame('<hello>content</hello>', $w->getForecastWeather('深圳', 'xml'));
    }

    public function testGetWeatherWithGuzzleRuntimeException()
    {
        $client = \Mockery::mock(Client::class);
        $client->allows()
            ->get(new AnyArgs())
            ->andThrow(new \Exception('request timeout'));

        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');

        $w->getWeather('深圳');
    }

    public function testGetHttpClient()
    {
        $w = new Weather('mock-key');

        // 断言返回结果为 GuzzleHttp\ClientInterface 实例
        $this->assertInstanceOf(ClientInterface::class, $w->getHttpClient());
    }

    public function testSetGuzzleOptions()
    {
        $w = new Weather('mock-key');

        // 设置参数前，timeout 为 null
        $this->assertNull($w->getHttpClient()->getConfig('timeout'));

        // 设置参数
        $w->setGuzzleOptions(['timeout' => 5000]);

        // 设置参数后，timeout 为 5000
        $this->assertSame(5000, $w->getHttpClient()->getConfig('timeout'));
    }
}
