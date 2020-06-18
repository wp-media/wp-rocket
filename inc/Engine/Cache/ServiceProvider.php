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
		'advanced_cache',
		'wp_cache',
		'purge',
		'purge_actions_subscriber',
		'admin_cache_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$filesystem = rocket_direct_filesystem();

		$this->getContainer()->add( 'advanced_cache', 'WP_Rocket\Engine\Cache\AdvancedCache' )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/cache/' )
			->withArgument( $filesystem );
		$this->getContainer()->add( 'wp_cache', 'WP_Rocket\Engine\Cache\WPCache' )
			->withArgument( $filesystem );
		$this->getContainer()->add( 'purge', 'WP_Rocket\Engine\Cache\Purge' )
			->withArgument( $filesystem );
		$this->getContainer()->share( 'purge_actions_subscriber', 'WP_Rocket\Engine\Cache\PurgeActionsSubscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( $this->getContainer()->get( 'purge' ) );
		$this->getContainer()->share( 'admin_cache_subscriber', 'WP_Rocket\Engine\Cache\AdminSubscriber' )
			->withArgument( $this->getContainer()->get( 'advanced_cache' ) )
			->withArgument( $this->getContainer()->get( 'wp_cache' ) );
	}
}
