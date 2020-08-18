<?php
namespace WP_Rocket\Engine\HealthCheck;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for health check subscribers
 *
 * @since 3.6
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
		'health_check',
		'cache_dir_size_check',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @since 3.6
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'health_check', 'WP_Rocket\Engine\HealthCheck\HealthCheck' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'cache_dir_size_check', 'WP_Rocket\Engine\HealthCheck\CacheDirSizeCheck' )
		->withArgument( rocket_get_constant( 'WP_ROCKET_MINIFY_CACHE_PATH' ) )
		->withArgument( rocket_get_constant( 'WP_ROCKET_WEB_MAIN' ) );
	}
}
