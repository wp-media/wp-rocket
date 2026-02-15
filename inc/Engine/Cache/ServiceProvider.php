<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Cache;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache;
use WP_Rocket\Engine\Cache\PurgeExpired\Subscriber;
use WP_Rocket\Engine\Preload\Database\Queries\Cache as CacheQuery;
use WP_Rocket\Engine\Cache\Config\ConfigSubscriber;
use WP_Rocket\Engine\Cache\UrlValidation\{ TaxonomySubscriber, PostSubscriber };

/**
 * Service Provider for cache subscribers
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
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
		'preload_caches_query',
		'cache_config',
		'taxonomy_subscriber',
		'post_subscriber',
	];

	/**
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$filesystem = rocket_direct_filesystem();

		$this->getContainer()->add( 'preload_caches_query', CacheQuery::class )
			->addArgument( 'logger' );

		$this->getContainer()->add( 'advanced_cache', AdvancedCache::class )
			->addArguments(
				[
					new StringArgument( $this->getContainer()->get( 'template_path' ) . '/cache/' ),
					$filesystem,
				]
			);
		$this->getContainer()->add( 'wp_cache', WPCache::class )
			->addArgument( $filesystem );
		$this->getContainer()->add( 'purge', Purge::class )
			->addArguments(
				[
					$filesystem,
					'preload_caches_query',
				]
			);
		$this->getContainer()->addShared( 'purge_actions_subscriber', PurgeActionsSubscriber::class )
			->addArguments(
				[
					'options',
					'purge',
				]
			);
		$this->getContainer()->addShared( 'admin_cache_subscriber', AdminSubscriber::class )
			->addArguments(
				[
					'advanced_cache',
					'wp_cache',
				]
			);
		$this->getContainer()->add( 'expired_cache_purge', PurgeExpiredCache::class )
			->addArgument( new StringArgument( rocket_get_constant( 'WP_ROCKET_CACHE_PATH', '' ) ) );
		$this->getContainer()->addShared( 'expired_cache_purge_subscriber', Subscriber::class )
			->addArguments(
				[
					'options',
					'expired_cache_purge',
				]
			);
		$this->getContainer()->addShared( 'cache_config', ConfigSubscriber::class )
			->addArguments(
				[
					'options',
					'options_api',
				]
			);
		$this->getContainer()->addShared( 'taxonomy_subscriber', TaxonomySubscriber::class );
		$this->getContainer()->addShared( 'post_subscriber', PostSubscriber::class );
	}
}
