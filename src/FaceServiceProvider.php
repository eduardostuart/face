<?php

namespace Face;

use Face\Contracts\Factory;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Foundation\Application as LaravelApplication;

class FaceServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $source = realpath(__DIR__.'/../config/face.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('face.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('face');
        }

        $this->mergeConfigFrom($source, 'face');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(Factory::class, function ($app) {
            return new FaceManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Factory::class];
    }
}
