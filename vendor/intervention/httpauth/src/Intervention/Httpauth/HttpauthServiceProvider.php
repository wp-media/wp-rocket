<?php

namespace Intervention\Httpauth;

use Illuminate\Support\ServiceProvider;

class HttpauthServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Actual provider
     *
     * @var \Illuminate\Support\ServiceProvider
     */
    protected $provider;

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->provider = $this->getProvider();
    }

     /**
     * Return ServiceProvider according to Laravel version
     *
     * @return \Intervention\Httpauth\Provider\ProviderInterface
     */
    private function getProvider()
    {
        $app = $this->app;
        $version = intval($app::VERSION);
        $provider = sprintf(
            '\Intervention\Httpauth\HttpauthServiceProviderLaravel%d', $version
        );

        return new $provider($app);
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        return $this->provider->boot();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        return $this->provider->register();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('httpauth');
    }

}
