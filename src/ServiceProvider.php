<?php

/*
 * This file is part of the luoweikingjj/weather.
 *
 * (c) luoweikingjj <i@luowe.top>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Luoweikingjj\Weather;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(
            Weather::class,
            function () {
                return new Weather(config('services.weather.key'));
            }
        );

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
