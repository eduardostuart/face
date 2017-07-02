<?php

namespace Face\Providers;

use Face\FacePlusPlus;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Foundation\Application as LaravelApplication;

class FaceServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath(__DIR__.'/../../config/face.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('face.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('face');
        }

        $this->mergeConfigFrom($source, 'face');
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('face', function ($app, $config) {
            return new FacePlusPlus(
                $app->config->get('face.api_key'),
                $app->config->get('face.api_secret')
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['face'];
    }
}
