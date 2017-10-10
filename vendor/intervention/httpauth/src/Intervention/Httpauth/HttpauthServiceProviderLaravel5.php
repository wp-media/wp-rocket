<?php

namespace Intervention\Httpauth;

use Illuminate\Support\ServiceProvider;

class HttpauthServiceProviderLaravel5 extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(array(
            __DIR__.'/../../config/config.php' => config_path('httpauth.php')
        ));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // merge default config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php', 'httpauth'
        );

        $this->app->singleton('httpauth', function ($app) {
            return new Httpauth($app['config']->get('httpauth'));
        });
    }
}
