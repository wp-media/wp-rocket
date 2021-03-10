<?php
namespace WP_Rocket\Engine\Cache;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

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
		'advanced_cache',
		'wp_cache',
		'purge',
		'purge_actions_subscriber',
		'admin_cache_subscriber',
		'expired_cache_purge',
		'expired_cache_purge_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$filesystem = rocket_direct_filesystem();

		$this->getContainer()->add( 'advanced_cache', 'WP_Rocket\Engine\Cache\AdvancedCache' )
			->addArgument( $this->getContainer()->get( 'template_path' ) . '/cache/' )
			->addArgument( $filesystem );
		$this->getContainer()->add( 'wp_cache', 'WP_Rocket\Engine\Cache\WPCache' )
			->addArgument( $filesystem );
		$this->getContainer()->add( 'purge', 'WP_Rocket\Engine\Cache\Purge' )
			->addArgument( $filesystem );
		$this->getContainer()->share( 'purge_actions_subscriber', 'WP_Rocket\Engine\Cache\PurgeActionsSubscriber' )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'purge' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()->share( 'admin_cache_subscriber', 'WP_Rocket\Engine\Cache\AdminSubscriber' )
			->addArgument( $this->getContainer()->get( 'advanced_cache' ) )
			->addArgument( $this->getContainer()->get( 'wp_cache' ) )
			->addTag( 'admin_subscriber' );

		$this->getContainer()->add( 'expired_cache_purge', 'WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache' )
			->addArgument( rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) );
		$this->getContainer()->share( 'expired_cache_purge_subscriber', 'WP_Rocket\Engine\Cache\PurgeExpired\Subscriber' )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'expired_cache_purge' ) )
			->addTag( 'common_subscriber' );
	}
}
