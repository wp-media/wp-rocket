<?php
namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket preload.
 *
 * @since 3.3
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
		'fonts_preload_subscriber',
		'preload_caches_table',
		'preload_caches_query',
	];

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register() {

		$this->getContainer()->add( 'preload_caches_table', 'WP_Rocket\Engine\Preload\Database\Tables\Cache' );
		$this->getContainer()->add( 'preload_caches_query', 'WP_Rocket\Engine\Preload\Database\Queries\Cache' );
		$this->getContainer()->get( 'preload_caches_table' );

		// Subscribers.
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->share( 'fonts_preload_subscriber', 'WP_Rocket\Engine\Preload\Fonts' )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cdn' ) )
			->addTag( 'common_subscriber' );
	}
}
