<?php
namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use ActionScheduler_Compatibility;
use ActionScheduler_Lock;
use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Logger\Logger;
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
		'preload_subscriber',
		'preload_front_subscriber',
		'fonts_preload_subscriber',
		'preload_caches_table',
		'preload_caches_query',
	];

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register() {

		$this->getContainer()->add( 'wp_direct_filesystem', '\WP_Filesystem_Direct' )
			->addArgument( [] );
		$wp_file_system = $this->getContainer()->get( 'wp_direct_filesystem' );
		$this->getContainer()->add( 'preload_caches_table', 'WP_Rocket\Engine\Preload\Database\Tables\Cache' );
		$this->getContainer()->add( 'preload_caches_query', 'WP_Rocket\Engine\Preload\Database\Queries\Cache' );
		$this->getContainer()->get( 'preload_caches_table' );
		// Subscribers.
		$options     = $this->getContainer()->get( 'options' );
		$cache_query = $this->getContainer()->get( 'preload_caches_query' );
		$this->getContainer()->add( 'preload_queue', 'WP_Rocket\Engine\Preload\Controller\Queue' );
		$queue = $this->getContainer()->get( 'preload_queue' );
		$this->getContainer()->add( 'sitemap_parser', 'WP_Rocket\Engine\Preload\Frontend\SitemapParser' );
		$sitemap_parser = $this->getContainer()->get( 'sitemap_parser' );
		$this->getContainer()->add( 'preload_url_controller', 'WP_Rocket\Engine\Preload\Controller\PreloadUrl' )
			->addArgument( $options )
			->addArgument( $queue )
			->addArgument( $cache_query )
			->addArgument( $wp_file_system );
		$this->getContainer()->add( 'parse_sitemap_controller', 'WP_Rocket\Engine\Preload\Frontend\ParseSitemap' )
			->addArgument( $sitemap_parser )
			->addArgument( $queue )
			->addArgument( $cache_query );
		$parse_sitemap_controller = $this->getContainer()->get( 'parse_sitemap_controller' );
		$preload_url_controller   = $this->getContainer()->get( 'preload_url_controller' );
		$this->getContainer()->add( 'preload_front_subscriber', 'WP_Rocket\Engine\Preload\Frontend\Subscriber' )
			->addArgument( $parse_sitemap_controller )
			->addArgument( $preload_url_controller )
			->addTag( 'common_subscriber' );
		$this->getContainer()->add( 'load_initial_sitemap_controller', 'WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap' )
			->addArgument( $queue );
		$this->getContainer()->add( 'full_preload_process', 'WP_Rocket\Engine\Preload\Subscriber' )
			->addArgument( $this->getContainer()->get( 'load_initial_sitemap_controller' ) )
			->addArgument( $cache_query )
			->addTag( 'common_subscriber' );
		$this->getContainer()->add( 'preload_settings', 'WP_Rocket\Engine\Preload\Admin\Settings' )
			->addArgument( $options );
		$preload_settings = $this->getContainer()->get( 'preload_settings' );

		$this->getContainer()->share(
			'preload_queue_runner',
			static function() {
				return new PreloadQueueRunner(
					null,
					null,
					null,
					null,
					new ActionScheduler_Compatibility(),
					new Logger(),
					ActionScheduler_Lock::instance()
				);
			}
		);

		$preload_queue_runner = $this->getContainer()->get( 'preload_queue_runner' );

		$this->getContainer()->add( 'preload_admin_subscriber', 'WP_Rocket\Engine\Preload\Admin\Subscriber' )
			->addArgument( $preload_settings )
			->addArgument( $queue )
			->addArgument( $preload_queue_runner )
			->addArgument( new Logger() )
			->addTag( 'common_subscriber' );

		$this->getContainer()->share( 'fonts_preload_subscriber', 'WP_Rocket\Engine\Preload\Fonts' )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cdn' ) )
			->addTag( 'common_subscriber' );

		$this->getContainer()->add( 'cron_subscriber', 'WP_Rocket\Engine\Preload\Cron\Subscriber' )
			->addArgument( $preload_settings )
			->addArgument( $cache_query )
			->addArgument( $preload_url_controller )
			->addTag( 'common_subscriber' );
	}
}
