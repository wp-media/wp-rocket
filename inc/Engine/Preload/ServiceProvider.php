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
use WP_Rocket\Engine\CDN\ServiceProvider as CDNServiceProvider;
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

	public function declare()
	{
		$this->register_service('preload_queue', function ($id) {
			$this->add( $id, Queue::class );
		});

		$this->register_service('preload_mobile_detect', function ($id) {
			$this->add( $id, WP_Rocket_Mobile_Detect::class );
		});

		$this->register_service('wp_direct_filesystem', function ($id) {
			$this->add( $id, WP_Filesystem_Direct::class )
				->addArgument( [] );
		});

		$this->register_service('preload_caches_table', function ($id) {
			$this->add( $id, CacheTable::class );
			$this->get_internal( 'preload_caches_table' );
		});

		$this->register_service('preload_caches_query', function ($id) {
			$this->add( $id, CacheQuery::class )
				->addArgument( new Logger() );
		});

		$this->register_service('preload_url_controller', function ($id) {
			$this->add( $id, PreloadUrl::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_internal( 'preload_queue' ) )
				->addArgument( $this->get_internal( 'preload_caches_query' ) )
				->addArgument( $this->get_internal( 'wp_direct_filesystem' ) );
		});

		$this->register_service('homepage_crawler', function ($id) {
			$this->add( $id, CrawlHomepage::class );
		});

		$this->register_service('sitemap_parser', function ($id) {
			$this->add( $id, SitemapParser::class );
		});

		$this->register_service('fetch_sitemap_controller', function ($id) {
			$this->add( $id, FetchSitemap::class )
				->addArgument( $this->get_internal( 'sitemap_parser' ) )
				->addArgument( $this->get_internal( 'preload_queue' ) )
				->addArgument( $this->get_internal( 'preload_caches_query' ) );
		});

		$this->register_service('load_initial_sitemap_controller', function ($id) {
			$this->add( $id, LoadInitialSitemap::class )
				->addArgument( $this->get_internal( 'preload_queue' ) )
				->addArgument( $this->get_internal( 'preload_caches_query' ) )
				->addArgument( $this->get_internal( 'homepage_crawler' ) );
		});

		$this->register_service('preload_activation', function ($id) {
			$this->add( $id, Activation::class )
				->addArgument( $this->get_internal( 'preload_queue' ) )
				->addArgument( $this->get_internal( 'preload_caches_query' ) )
				->addArgument( $this->get_external( 'options' ) );
		});

		$this->register_service('preload_settings', function ($id) {
			$this->add( $id, Settings::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_internal( 'preload_url_controller' ) );
		});

		$this->register_service('check_finished_controller', function ($id) {
			$this->add( $id, CheckFinished::class )
				->addArgument( $this->get_internal( 'preload_settings' ) )
				->addArgument( $this->get_internal( 'preload_caches_query' ) )
				->addArgument( $this->get_internal( 'preload_queue' ) );
		});

		$this->register_service('preload_front_subscriber', function ($id) {
			$this->share( $id, FrontEndSubscriber::class )
				->addArgument( $this->get_internal( 'fetch_sitemap_controller' ) )
				->addArgument( $this->get_internal( 'preload_url_controller' ) )
				->addArgument( $this->get_internal( 'check_finished_controller' ) )
				->addArgument( $this->get_internal( 'load_initial_sitemap_controller' ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('preload_clean_controller', function ($id) {
			$this->add( $id, ClearCache::class )
				->addArgument( $this->get_internal( 'preload_caches_query' ) );
		});

		$this->register_service('preload_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_internal( 'load_initial_sitemap_controller' ) )
				->addArgument( $this->get_internal( 'preload_caches_query' ) )
				->addArgument( $this->get_internal( 'preload_activation' ) )
				->addArgument( $this->get_internal( 'preload_mobile_detect' ) )
				->addArgument( $this->get_internal( 'preload_clean_controller' ) )
				->addArgument( $this->get_internal( 'preload_queue' ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('preload_cron_subscriber', function ($id) {
			$this->share( $id, CronSubscriber::class )
				->addArgument( $this->get_internal( 'preload_settings' ) )
				->addArgument( $this->get_internal( 'preload_caches_query' ) )
				->addArgument( $this->get_internal( 'preload_url_controller' ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('fonts_preload_subscriber', function ($id) {
			$this->share( $id, Fonts::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_external( 'cdn', CDNServiceProvider::class ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('preload_admin_subscriber', function ($id) {
			$this->add( $id, AdminSubscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_internal( 'preload_settings' ) )
				->addTag( 'common_subscriber' );
		});

	}
}
