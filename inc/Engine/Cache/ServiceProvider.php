<?php
namespace WP_Rocket\Engine\Cache;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache;
use WP_Rocket\Engine\Cache\PurgeExpired\Subscriber;
use WP_Rocket\Engine\Cache\Config\ConfigSubscriber;

/**
 * Service Provider for cache subscribers
 *
 * @since 3.5.5
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('purge_actions_subscriber'),
			$this->generate_container_id('expired_cache_purge_subscriber'),
			$this->generate_container_id('cache_config'),
		];
	}

	public function get_admin_subscribers(): array
	{
		return [
			$this->generate_container_id('admin_cache_subscriber')
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$filesystem = rocket_direct_filesystem();

		$this->add( 'advanced_cache', AdvancedCache::class )
			->addArgument( $this->getContainer()->get( 'template_path' ) . '/cache/' )
			->addArgument( $filesystem );
		$this->add( 'wp_cache', WPCache::class )
			->addArgument( $filesystem );
		$this->add( 'purge', Purge::class )
			->addArgument( $filesystem );
		$this->share( 'purge_actions_subscriber', PurgeActionsSubscriber::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->get_internal( 'purge' ) )
			->addTag( 'common_subscriber' );
		$this->share( 'admin_cache_subscriber', AdminSubscriber::class )
			->addArgument( $this->get_internal( 'advanced_cache' ) )
			->addArgument( $this->get_internal( 'wp_cache' ) )
			->addTag( 'admin_subscriber' );

		$this->add( 'expired_cache_purge', PurgeExpiredCache::class )
			->addArgument( rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) );
		$this->share( 'expired_cache_purge_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->get_internal( 'expired_cache_purge' ) )
			->addTag( 'common_subscriber' );
		$this->add( 'cache_config', ConfigSubscriber::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addTag( 'common_subscriber' );
	}
}
