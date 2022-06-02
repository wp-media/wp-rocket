<?php
namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

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
		'fonts_preload_subscriber',
		'preload_front_subscriber',
		'preload_queue',
		'sitemap_parser',
		'parse_sitemap_controller',
		'load_initial_sitemap_controller',
		'preload_admin_subscriber',
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
		// Subscribers.
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'preload_settings', 'WP_Rocket\Engine\Preload\Admin\Settings' )
			->addArgument( $options );
		$preload_settings = $this->getContainer()->get( 'preload_settings' );

		$this->getContainer()->add( 'full_preload_process', 'WP_Rocket\Engine\Preload\FullProcess' );
		$this->getContainer()->add( 'partial_preload_process', 'WP_Rocket\Engine\Preload\PartialProcess' );

		$this->getContainer()->add( 'preload_caches_table', 'WP_Rocket\Engine\Preload\Database\Tables\Cache' );
		$this->getContainer()->add( 'preload_caches_query', 'WP_Rocket\Engine\Preload\Database\Queries\Cache' );
		$this->getContainer()->get( 'preload_caches_table' );

		$full_preload_process = $this->getContainer()->get( 'full_preload_process' );
		$this->getContainer()->add( 'homepage_preload', 'WP_Rocket\Engine\Preload\Homepage' )
			->addArgument( $full_preload_process );
		$this->getContainer()->add( 'sitemap_preload', 'WP_Rocket\Engine\Preload\Sitemap' )
			->addArgument( $full_preload_process );

		$cache_query = $this->getContainer()->get( 'preload_caches_query' );
		$this->getContainer()->add( 'preload_queue', 'WP_Rocket\Engine\Preload\Controller\Queue' );
		$queue = $this->getContainer()->get( 'preload_queue' );
		$this->getContainer()->add( 'sitemap_parser', 'WP_Rocket\Engine\Preload\Frontend\SitemapParser' );
		$sitemap_parser = $this->getContainer()->get( 'sitemap_parser' );
		$this->getContainer()->add( 'parse_sitemap_controller', 'WP_Rocket\Engine\Preload\Frontend\ParseSitemap' )
			->addArgument( $sitemap_parser )
			->addArgument( $queue )
			->addArgument( $cache_query );

		$this->getContainer()->add( 'check_finished_controller', 'WP_Rocket\Engine\Preload\Controller\CheckFinished' )
			->addArgument( $preload_settings )
			->addArgument( $cache_query )
			->addArgument( $queue );

		$check_finished_controller = $this->getContainer()->get( 'check_finished_controller' );

		$parse_sitemap_controller = $this->getContainer()->get( 'parse_sitemap_controller' );
		$this->getContainer()->add( 'load_initial_sitemap_controller', 'WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap' )
			->addArgument( $queue );
		$this->getContainer()->add( 'preload_front_subscriber', 'WP_Rocket\Engine\Preload\Frontend\Subscriber' )
			->addArgument( $parse_sitemap_controller )
			->addArgument( $check_finished_controller )
			->addTag( 'common_subscriber' );
		$this->getContainer()->add( 'preload_subscriber', 'WP_Rocket\Engine\Preload\Subscriber' )
			->addArgument( $this->getContainer()->get( 'load_initial_sitemap_controller' ) )
			->addTag( 'common_subscriber' );

		$this->getContainer()->add( 'preload_admin_subscriber', 'WP_Rocket\Engine\Preload\Admin\Subscriber' )
			->addArgument( $preload_settings )
			->addTag( 'common_subscriber' );
		$this->getContainer()->add( 'partial_preload_process', 'WP_Rocket\Engine\Preload\PartialProcess' );
		$this->getContainer()->share( 'fonts_preload_subscriber', 'WP_Rocket\Engine\Preload\Fonts' )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cdn' ) )
			->addTag( 'common_subscriber' );
	}
}
