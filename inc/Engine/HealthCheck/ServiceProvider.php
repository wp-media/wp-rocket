<?php
namespace WP_Rocket\Engine\HealthCheck;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

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
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'health_check', HealthCheck::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->share( 'cache_dir_size_check', CacheDirSizeCheck::class )
			->addArgument( rocket_get_constant( 'WP_ROCKET_MINIFY_CACHE_PATH' ) )
			->addArgument( rocket_get_constant( 'WP_ROCKET_WEB_MAIN' ) )
			->addTag( 'common_subscriber' );
	}
}
