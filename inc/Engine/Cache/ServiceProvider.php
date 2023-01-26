<?php
namespace WP_Rocket\Engine\Cache;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache;
use WP_Rocket\Engine\Cache\PurgeExpired\Subscriber;
use WP_Rocket\Engine\Preload\Database\Queries\Cache as CacheQuery;
use WP_Rocket\Logger\Logger;
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

	public function declare()
	{
		$filesystem = rocket_direct_filesystem();

		$this->register_service('advanced_cache', function($id) use ($filesystem) {
			$this->add( $id, AdvancedCache::class )
				->addArgument( $this->get_external( 'template_path' ) . '/cache/' )
				->addArgument( $filesystem );
		});

		$this->register_service('wp_cache', function($id) use ($filesystem) {
			$this->add( $id, WPCache::class )
				->addArgument( $filesystem );
		});

		$this->register_service('purge', function($id) use ($filesystem) {
			$this->add( $id, Purge::class )
				->addArgument( $filesystem )
        ->addArgument( $this->get_internal( 'preload_caches_query' )  );
		});

		$this->register_service('purge_actions_subscriber', function($id) {
			$this->share( $id, PurgeActionsSubscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_internal( 'purge' ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('admin_cache_subscriber', function($id) {
			$this->share( $id, AdminSubscriber::class )
				->addArgument( $this->get_internal( 'advanced_cache' ) )
				->addArgument( $this->get_internal( 'wp_cache' ) )
				->addTag( 'admin_subscriber' );
		});

		$this->register_service('expired_cache_purge', function($id) {
			$this->add( $id, PurgeExpiredCache::class )
				->addArgument( rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) );
		});

		$this->register_service('expired_cache_purge_subscriber', function($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_internal( 'expired_cache_purge' ) )
				->addTag( 'common_subscriber' );
		});
    
    $this->register_service('preload_caches_query', function($id) {
			$this->share( $id, CacheQuery::class )
				->addArgument( new Logger() );
		});

		$this->register_service('cache_config', function($id) {
			$this->add( $id, ConfigSubscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_external( 'options_api' ) )
				->addTag( 'common_subscriber' );
		});
	}
}
