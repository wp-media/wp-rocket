<?php
namespace WP_Rocket\Engine\Deactivation;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * Service Provider for the activation process.
 *
 * @since 3.6.3
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {

	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'capabilities_manager',
		'wp_cache',
    ];

    public function boot() {
        $this->getContainer()
            ->inflector( 'WP_Rocket\Engine\Deactivation\DeactivationInterface' )
            ->invokeMethod( 'deactivate', [] );

    }

	/**
	 * Registers the option array in the container.
	 *
	 * @since 3.3
	 */
	public function register() {
        $this->getContainer()->add( 'capabilities_manager', 'WP_Rocket\Engine\Capabilities\Manager' );
        $this->getContainer()->add( 'wp_cache', 'WP_Rocket\Engine\Cache\WPCache' )
            ->withArgument( rocket_direct_filesystem() );
	}
}
