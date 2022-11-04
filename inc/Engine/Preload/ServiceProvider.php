<?php
namespace WP_Rocket\Engine\Preload;

use WP_Filesystem_Direct;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Admin\Subscriber as AdminSubscriber;
use WP_Rocket\Engine\Preload\Controller\CheckFinished;
use WP_Rocket\Engine\Preload\Controller\ClearCache;
use WP_Rocket\Engine\Preload\Controller\CrawlHomepage;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Cron\Subscriber as CronSubscriber;
use WP_Rocket\Engine\Preload\Database\Queries\Cache as CacheQuery;
use WP_Rocket\Engine\Preload\Database\Tables\Cache as CacheTable;
use WP_Rocket\Engine\Preload\Frontend\FetchSitemap;
use WP_Rocket\Engine\Preload\Frontend\SitemapParser;
use WP_Rocket\Engine\Preload\Frontend\Subscriber as FrontEndSubscriber;
use WP_Rocket\Logger\Logger;
use WP_Rocket_Mobile_Detect;

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
		'fonts_preload_subscriber',
		'preload_activation',
	];

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'preload_mobile_detect', WP_Rocket_Mobile_Detect::class );

		$this->getContainer()->add( 'wp_direct_filesystem', WP_Filesystem_Direct::class )
			->addArgument( [] );
		$wp_file_system = $this->getContainer()->get( 'wp_direct_filesystem' );

		$this->getContainer()->add( 'preload_caches_table', CacheTable::class );
		$this->getContainer()->add( 'preload_caches_query', CacheQuery::class )
			->addArgument( new Logger() );
		$this->getContainer()->get( 'preload_caches_table' );

		$cache_query = $this->getContainer()->get( 'preload_caches_query' );

		$this->getContainer()->add( 'preload_queue', Queue::class );
		$queue = $this->getContainer()->get( 'preload_queue' );

		$this->getContainer()->add( 'preload_url_controller', PreloadUrl::class )
			->addArgument( $options )
			->addArgument( $queue )
			->addArgument( $cache_query )
			->addArgument( $wp_file_system );

		$preload_url_controller = $this->getContainer()->get( 'preload_url_controller' );

		$this->getContainer()->add( 'homepage_crawler', CrawlHomepage::class );
		$crawl_homepage = $this->getContainer()->get( 'homepage_crawler' );

		$this->getContainer()->add( 'sitemap_parser', SitemapParser::class );
		$sitemap_parser = $this->getContainer()->get( 'sitemap_parser' );

		$this->getContainer()->add( 'fetch_sitemap_controller', FetchSitemap::class )
			->addArgument( $sitemap_parser )
			->addArgument( $queue )
			->addArgument( $cache_query );

		$fetch_sitemap_controller = $this->getContainer()->get( 'fetch_sitemap_controller' );

		$this->getContainer()->add( 'load_initial_sitemap_controller', LoadInitialSitemap::class )
			->addArgument( $queue )
			->addArgument( $cache_query )
			->addArgument( $crawl_homepage );

		$this->getContainer()->add( 'preload_activation', Activation::class )
			->addArgument( $queue )
			->addArgument( $cache_query )
			->addArgument( $options );

		$this->getContainer()->add( 'preload_settings', Settings::class )
			->addArgument( $options )
			->addArgument( $preload_url_controller );

		$preload_settings = $this->getContainer()->get( 'preload_settings' );

		$this->getContainer()->add( 'check_finished_controller', CheckFinished::class )
			->addArgument( $preload_settings )
			->addArgument( $cache_query )
			->addArgument( $queue );

		$check_finished_controller = $this->getContainer()->get( 'check_finished_controller' );

		$this->getContainer()->share( 'preload_front_subscriber', FrontEndSubscriber::class )
			->addArgument( $fetch_sitemap_controller )
			->addArgument( $preload_url_controller )
			->addArgument( $check_finished_controller )
			->addArgument( $this->getContainer()->get( 'load_initial_sitemap_controller' ) )
			->addTag( 'common_subscriber' );

		$this->getContainer()->add( 'preload_clean_controller', ClearCache::class )
			->addArgument( $cache_query );

		$clean_controller = $this->getContainer()->get( 'preload_clean_controller' );

		$this->getContainer()->share( 'preload_subscriber', Subscriber::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'load_initial_sitemap_controller' ) )
			->addArgument( $cache_query )
			->addArgument( $this->getContainer()->get( 'preload_activation' ) )
			->addArgument( $this->getContainer()->get( 'preload_mobile_detect' ) )
			->addArgument( $clean_controller )
			->addArgument( $queue )
			->addTag( 'common_subscriber' );

		$this->getContainer()->share( 'preload_cron_subscriber', CronSubscriber::class )
			->addArgument( $preload_settings )
			->addArgument( $cache_query )
			->addArgument( $preload_url_controller )
			->addTag( 'common_subscriber' );

		$this->getContainer()->share( 'fonts_preload_subscriber', Fonts::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cdn' ) )
			->addTag( 'common_subscriber' );

		$this->getContainer()->add( 'preload_admin_subscriber', AdminSubscriber::class )
			->addArgument( $options )
			->addArgument( $preload_settings )
			->addTag( 'common_subscriber' );

	}
}
