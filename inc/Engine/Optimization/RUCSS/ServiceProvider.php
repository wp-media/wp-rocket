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
		'rucss_resources_query',
		'rucss_queue',
		'rucss_cron_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'rucss_settings', 'WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings' )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) );
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
		$this->getContainer()->add( 'rucss_queue', 'WP_Rocket\Engine\Optimization\RUCSS\Controller\Queue' );

		$this->getContainer()->add( 'rucss_used_css_controller', 'WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS' )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_query' ) )
			->addArgument( $this->getContainer()->get( 'rucss_resources_query' ) )
			->addArgument( $this->getContainer()->get( 'rucss_frontend_api_client' ) )
			->addArgument( $this->getContainer()->get( 'rucss_queue' ) );

		$this->getContainer()->share( 'rucss_admin_subscriber', 'WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber' )
			->addArgument( $this->getContainer()->get( 'rucss_settings' ) )
			->addArgument( $this->getContainer()->get( 'rucss_database' ) )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_controller' ) )
			->addArgument( $this->getContainer()->get( 'rucss_queue' ) );
		$this->getContainer()->share( 'rucss_frontend_subscriber', 'WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber' )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_controller' ) );
		$this->getContainer()->share( 'rucss_cron_subscriber', 'WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber' )
			->addArgument( $this->getContainer()->get( 'rucss_used_css_controller' ) )
			->addArgument( $this->getContainer()->get( 'rucss_database' ) );
	}
}
