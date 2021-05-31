<?php
namespace WP_Rocket\Engine\Optimization\RUCSS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket RUCSS
 *
 * @since  3.9
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
		'rucss_settings',
		'rucss_resources_table',
		'rucss_database',
		'rucss_admin_subscriber',
		'rucss_frontend_api_client',
		'rucss_used_css',
		'rucss_used_css_query',
		'rucss_frontend_subscriber',
		'local_cache',
		'rucss_resource_fetcher',
		'rucss_resources_query',
		'rucss_warmup_api_client',
		'rucss_warmup_restwp',
		'rucss_scanner',
		'rucss_scanner_process',
		'rucss_status_checker',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'rucss_settings', 'WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings' )
			->addArgument( $this->getContainer()->get( 'options' ) );
		// Instantiate the RUCSS Resources Table class.
		$this->getContainer()->add( 'rucss_resources_table', 'WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources' );
		$this->getContainer()->add( 'rucss_usedcss_table', 'WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS' );
		$this->getContainer()->add( 'rucss_resources_query', 'WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery' );
		$this->getContainer()->add( 'rucss_database', 'WP_Rocket\Engine\Optimization\RUCSS\Admin\Database' )
			->addArgument( $this->getContainer()->get( 'rucss_resources_table' ) )
			->addArgument( $this->getContainer()->get( 'rucss_usedcss_table' ) );

		$this->getContainer()->add( 'rucss_used_css_query', 'WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS' );
		$this->getContainer()->add( 'rucss_frontend_api_client', 'WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient' )
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'rucss_used_css_controller', 'WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS' )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_query' ) )
			->addArgument( $this->getContainer()->get( 'rucss_resources_query' ) )
			->addArgument( $this->getContainer()->get( 'purge' ) )
			->addArgument( $this->getContainer()->get( 'rucss_frontend_api_client' ) );

		$this->getContainer()->share( 'rucss_admin_subscriber', 'WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber' )
			->addArgument( $this->getContainer()->get( 'rucss_settings' ) )
			->addArgument( $this->getContainer()->get( 'rucss_database' ) )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_controller' ) )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'homepage_preload' ) );
		$this->getContainer()->share( 'rucss_frontend_subscriber', 'WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber' )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_controller' ) );

		$this->getContainer()->add( 'local_cache', '\WP_Rocket\Engine\Optimization\AssetsLocalCache' )
			->addArgument( rocket_get_constant( 'WP_ROCKET_MINIFY_CACHE_PATH' ) )
			->addArgument( rocket_direct_filesystem() );

		$this->getContainer()->add( 'rucss_warmup_api_client', '\WP_Rocket\Engine\Optimization\RUCSS\Warmup\APIClient' )
			->addArgument( $this->getContainer()->get( 'options' ) );

		$this->getContainer()->add( 'rucss_resource_fetcher_process', '\WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcherProcess' )
			->addArgument( $this->getContainer()->get( 'rucss_resources_query' ) )
			->addArgument( $this->getContainer()->get( 'rucss_warmup_api_client' ) )
			->addArgument( $this->getContainer()->get( 'options_api' ) );

		$this->getContainer()->share( 'rucss_resource_fetcher', '\WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcher' )
			->addArgument( $this->getContainer()->get( 'local_cache' ) )
			->addArgument( $this->getContainer()->get( 'rucss_resource_fetcher_process' ) )
			->addArgument( $this->getContainer()->get( 'options_api' ) );

		$this->getContainer()->share( 'rucss_warmup_restwp', '\WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status\RESTWP' )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'rucss_resources_query' ) )
			->addArgument( $this->getContainer()->get( 'options_api' ) );

		$this->getContainer()->add( 'rucss_scanner_process', '\WP_Rocket\Engine\Optimization\RUCSS\Warmup\ScannerProcess' )
			->addArgument( $this->getContainer()->get( 'rucss_resource_fetcher' ) )
			->addArgument( $this->getContainer()->get( 'options_api' ) );
		$this->getContainer()->add( 'rucss_scanner', '\WP_Rocket\Engine\Optimization\RUCSS\Warmup\Scanner' )
			->addArgument( $this->getContainer()->get( 'rucss_scanner_process' ) )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'rucss_resources_table' ) )
			->addArgument( $this->getContainer()->get( 'options' ) );

		$this->getContainer()->add( 'rucss_status_checker', '\WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status\Checker' )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'rucss_resources_query' ) );

		$this->getContainer()->share( 'rucss_warmup_subscriber', '\WP_Rocket\Engine\Optimization\RUCSS\Warmup\Subscriber' )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'rucss_resource_fetcher' ) )
			->addArgument( $this->getContainer()->get( 'rucss_warmup_restwp' ) )
			->addArgument( $this->getContainer()->get( 'rucss_scanner' ) )
			->addArgument( $this->getContainer()->get( 'rucss_status_checker' ) );
	}
}
