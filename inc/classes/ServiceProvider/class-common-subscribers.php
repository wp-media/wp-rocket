<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket features common for admin and front
 *
 * @since 3.3
 * @author Remy Perona
 */
class Common_Subscribers extends AbstractServiceProvider {

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
		'heartbeat_subscriber',
		'db_optimization_subscriber',
		'capabilities_subscriber',
		'webp_subscriber',
		'expired_cache_purge',
		'expired_cache_purge_subscriber',
		'detect_missing_tags',
	];

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->share( 'heartbeat_subscriber', 'WP_Rocket\Subscriber\Heartbeat_Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'db_optimization_subscriber', 'WP_Rocket\Subscriber\Admin\Database\Optimization_Subscriber' )
			->withArgument( $this->getContainer()->get( 'db_optimization' ) )
			->withArgument( $options );
		$this->getContainer()->add( 'expired_cache_purge', 'WP_Rocket\Cache\Expired_Cache_Purge' )
			->withArgument( rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) );
		$this->getContainer()->share( 'expired_cache_purge_subscriber', 'WP_Rocket\Subscriber\Cache\Expired_Cache_Purge_Subscriber' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'expired_cache_purge' ) );
		$this->getContainer()->share( 'capabilities_subscriber', 'WP_Rocket\Subscriber\Plugin\Capabilities_Subscriber' );
		$this->getContainer()->share( 'webp_subscriber', 'WP_Rocket\Subscriber\Media\Webp_Subscriber' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'options_api' ) )
			->withArgument( $this->getContainer()->get( 'cdn_subscriber' ) )
			->withArgument( $this->getContainer()->get( 'beacon' ) );
		$this->getContainer()->share( 'detect_missing_tags_subscriber', 'WP_Rocket\Subscriber\Tools\Detect_Missing_Tags_Subscriber' );
	}
}
