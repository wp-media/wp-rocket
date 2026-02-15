<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Admin\Subscriber as AdminSubscriber;
use WP_Rocket\Engine\Preload\Controller\{CheckFinished, ClearCache, CrawlHomepage, LoadInitialSitemap, PreloadUrl, Queue};
use WP_Rocket\Engine\Preload\Cron\Subscriber as CronSubscriber;
use WP_Rocket\Engine\Preload\Database\Queries\Cache as CacheQuery;
use WP_Rocket\Engine\Preload\Database\Tables\Cache as CacheTable;
use WP_Rocket\Engine\Preload\Frontend\FetchSitemap;
use WP_Rocket\Engine\Preload\Frontend\SitemapParser;
use WP_Rocket\Engine\Preload\Frontend\Subscriber as FrontEndSubscriber;
use WP_Rocket_Mobile_Detect;

/**
 * Service provider for the WP Rocket preload.
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'mobile_detect',
		'preload_queue',
		'sitemap_parser',
		'fetch_sitemap_controller',
		'check_finished_controller',
		'load_initial_sitemap_controller',
		'preload_url_controller',
		'preload_caches_table',
		'preload_caches_query',
		'preload_admin_subscriber',
		'preload_clean_controller',
		'preload_subscriber',
		'preload_front_subscriber',
		'preload_cron_subscriber',
		'preload_activation',
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
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'mobile_detect', WP_Rocket_Mobile_Detect::class );
		$this->getContainer()->add( 'preload_caches_table', CacheTable::class );
		$this->getContainer()->add( 'preload_caches_query', CacheQuery::class )
			->addArgument( 'logger' );
		$this->getContainer()->get( 'preload_caches_table' );

		$this->getContainer()->add( 'preload_queue', Queue::class );
		$this->getContainer()->add( 'preload_url_controller', PreloadUrl::class )
			->addArguments(
				[
					'options',
					'preload_queue',
					'preload_caches_query',
					rocket_direct_filesystem(),
				]
			);
		$this->getContainer()->add( 'homepage_crawler', CrawlHomepage::class );
		$this->getContainer()->add( 'sitemap_parser', SitemapParser::class );
		$this->getContainer()->add( 'fetch_sitemap_controller', FetchSitemap::class )
			->addArguments(
				[
					'sitemap_parser',
					'preload_queue',
					'preload_caches_query',
				]
			);
		$this->getContainer()->add( 'load_initial_sitemap_controller', LoadInitialSitemap::class )
			->addArguments(
				[
					'preload_queue',
					'preload_caches_query',
					'homepage_crawler',
				]
			);
		$this->getContainer()->add( 'preload_activation', Activation::class )
			->addArguments(
				[
					'preload_url_controller',
					'preload_queue',
					'preload_caches_query',
					'options',
				]
			);
		$this->getContainer()->add( 'preload_settings', Settings::class )
			->addArguments(
				[
					'options',
					'preload_url_controller',
					'load_initial_sitemap_controller',
					'preload_caches_table',
				]
			);
		$this->getContainer()->add( 'check_finished_controller', CheckFinished::class )
			->addArguments(
				[
					'preload_settings',
					'preload_caches_query',
					'preload_queue',
				]
			);
		$this->getContainer()->addShared( 'preload_front_subscriber', FrontEndSubscriber::class )
			->addArguments(
				[
					'fetch_sitemap_controller',
					'preload_url_controller',
					'check_finished_controller',
					'load_initial_sitemap_controller',
				]
			);
		$this->getContainer()->add( 'preload_clean_controller', ClearCache::class )
			->addArgument( 'preload_caches_query' );
		$this->getContainer()->addShared( 'preload_subscriber', Subscriber::class )
			->addArguments(
				[
					'options',
					'load_initial_sitemap_controller',
					'preload_caches_query',
					'preload_activation',
					'mobile_detect',
					'preload_clean_controller',
					'preload_queue',
				]
			);
		$this->getContainer()->addShared( 'preload_cron_subscriber', CronSubscriber::class )
			->addArguments(
				[
					'preload_settings',
					'preload_caches_query',
					'preload_url_controller',
				]
			);
		$this->getContainer()->addShared( 'preload_admin_subscriber', AdminSubscriber::class )
			->addArgument( 'preload_settings' );
	}
}
