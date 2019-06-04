<?php

namespace Luoweikingjj\Weather;

/**
 * Class ServiceProvider
 * @package Luoweikingjj\Weather
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     *
     */
    public function register()
    {
        $this->app->singleton(Weather::class, function () {
            return new Weather(config('services.weather.key'));
        });

        $this->app->alias(Weather::class, 'weather');
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [Weather::class, 'weather'];
    }
}