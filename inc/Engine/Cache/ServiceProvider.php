<?php
namespace WP_Rocket\Engine\Cache;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for cache subscribers.
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
		'purge_actions_subscriber',
		'admin_cache_subscriber',
		'expired_cache_purge',
		'expired_cache_purge_subscriber',
	];

	/**
	 * Registers the option array in the container.
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->share( 'purge_actions_subscriber', 'WP_Rocket\Engine\Cache\PurgeActionsSubscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'admin_cache_subscriber', 'WP_Rocket\Engine\Cache\AdminSubscriber' );

		$this->getContainer()->add( 'expired_cache_purge', 'WP_Rocket\Engine\Cache\ExpiredCachePurge' )
			->withArgument( _rocket_get_wp_rocket_cache_path() )
			->withArgument( rocket_direct_filesystem() );
		$this->getContainer()->share( 'expired_cache_purge_subscriber', 'WP_Rocket\Engine\Cache\ExpiredCachePurgeSubscriber' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'expired_cache_purge' ) );
	}
}
