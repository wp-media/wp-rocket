<?php
namespace WP_Rocket\Engine\Preload;

use WP_Filesystem_Direct;
use WP_Rocket\AbstractServiceProvider;
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

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('preload_front_subscriber'),
			$this->generate_container_id('preload_subscriber'),
			$this->generate_container_id('preload_cron_subscriber'),
			$this->generate_container_id('fonts_preload_subscriber'),
			$this->generate_container_id('preload_admin_subscriber'),
		];
	}

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->add( 'preload_mobile_detect', WP_Rocket_Mobile_Detect::class );

		$this->add( 'wp_direct_filesystem', WP_Filesystem_Direct::class )
			->addArgument( [] );
		$wp_file_system = $this->get_internal( 'wp_direct_filesystem' );

		$this->add( 'preload_caches_table', CacheTable::class );
		$this->add( 'preload_caches_query', CacheQuery::class )
			->addArgument( new Logger() );
		$this->get_internal( 'preload_caches_table' );

		$cache_query = $this->get_internal( 'preload_caches_query' );

		$this->add( 'preload_queue', Queue::class );
		$queue = $this->get_internal( 'preload_queue' );

		$this->add( 'preload_url_controller', PreloadUrl::class )
			->addArgument( $options )
			->addArgument( $queue )
			->addArgument( $cache_query )
			->addArgument( $wp_file_system );

		$preload_url_controller = $this->get_internal( 'preload_url_controller' );

		$this->add( 'homepage_crawler', CrawlHomepage::class );
		$crawl_homepage = $this->get_internal( 'homepage_crawler' );

		$this->add( 'sitemap_parser', SitemapParser::class );
		$sitemap_parser = $this->get_internal( 'sitemap_parser' );

		$this->add( 'fetch_sitemap_controller', FetchSitemap::class )
			->addArgument( $sitemap_parser )
			->addArgument( $queue )
			->addArgument( $cache_query );

		$fetch_sitemap_controller = $this->get_internal( 'fetch_sitemap_controller' );

		$this->add( 'load_initial_sitemap_controller', LoadInitialSitemap::class )
			->addArgument( $queue )
			->addArgument( $cache_query )
			->addArgument( $crawl_homepage );

		$this->add( 'preload_activation', Activation::class )
			->addArgument( $queue )
			->addArgument( $cache_query )
			->addArgument( $options );

		$this->add( 'preload_settings', Settings::class )
			->addArgument( $options )
			->addArgument( $preload_url_controller );

		$preload_settings = $this->get_internal( 'preload_settings' );

		$this->add( 'check_finished_controller', CheckFinished::class )
			->addArgument( $preload_settings )
			->addArgument( $cache_query )
			->addArgument( $queue );

		$check_finished_controller = $this->get_internal( 'check_finished_controller' );

		$this->share( 'preload_front_subscriber', FrontEndSubscriber::class )
			->addArgument( $fetch_sitemap_controller )
			->addArgument( $preload_url_controller )
			->addArgument( $check_finished_controller )
			->addArgument( $this->get_internal( 'load_initial_sitemap_controller' ) )
			->addTag( 'common_subscriber' );

		$this->add( 'preload_clean_controller', ClearCache::class )
			->addArgument( $cache_query );

		$clean_controller = $this->get_internal( 'preload_clean_controller' );

		$this->share( 'preload_subscriber', Subscriber::class )
			->addArgument( $options )
			->addArgument( $this->get_internal( 'load_initial_sitemap_controller' ) )
			->addArgument( $cache_query )
			->addArgument( $this->get_internal( 'preload_activation' ) )
			->addArgument( $this->get_internal( 'preload_mobile_detect' ) )
			->addArgument( $clean_controller )
			->addArgument( $queue )
			->addTag( 'common_subscriber' );

		$this->share( 'preload_cron_subscriber', CronSubscriber::class )
			->addArgument( $preload_settings )
			->addArgument( $cache_query )
			->addArgument( $preload_url_controller )
			->addTag( 'common_subscriber' );

		$this->share( 'fonts_preload_subscriber', Fonts::class )
			->addArgument( $options )
			->addArgument( $this->get_internal( 'cdn' ) )
			->addTag( 'common_subscriber' );

		$this->add( 'preload_admin_subscriber', AdminSubscriber::class )
			->addArgument( $options )
			->addArgument( $preload_settings )
			->addTag( 'common_subscriber' );

	}
}
