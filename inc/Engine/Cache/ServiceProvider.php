<?php
namespace WP_Rocket\Engine\Cache;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for cache subscribers
 *
 * @since 3.5.5
 */
class ServiceProvider extends AbstractServiceProvider {

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
		'admin_cache_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'admin_cache_subscriber', 'WP_Rocket\Engine\Cache\AdminSubscriber' );
	}
}
