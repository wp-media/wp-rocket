<?php
namespace WP_Rocket\Engine\Optimization\RUCSS;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

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
		'rucss_api_client',
		'rucss_used_css',
		'rucss_used_css_query',
		'rucss_frontend_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'rucss_settings', 'WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		// Instantiate the RUCSS Resources Table class.
		$this->getContainer()->add( 'rucss_resources_table', 'WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources' );
		$this->getContainer()->add( 'rucss_usedcss_table', 'WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS' );
		$this->getContainer()->add( 'rucss_database', 'WP_Rocket\Engine\Optimization\RUCSS\Admin\Database' )
			->withArgument( $this->getContainer()->get( 'rucss_resources_table' ) )
			->withArgument( $this->getContainer()->get( 'rucss_usedcss_table' ) );
		$this->getContainer()->share( 'rucss_admin_subscriber', 'WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber' )
			->withArgument( $this->getContainer()->get( 'rucss_settings' ) )
			->withArgument( $this->getContainer()->get( 'rucss_database' ) );

		$this->getContainer()->add( 'rucss_used_css_query', 'WP_Rocket\Engine\Optimization\RUCSS\Database\Query\UsedCSS' );
		$this->getContainer()->add( 'rucss_api_client', 'WP_Rocket\Engine\Optimization\RUCSS\APIClient' );
		$this->getContainer()->add( 'rucss_used_css_controller', 'WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS' )
			->withArgument( $this->getContainer()->get( 'rucss_used_css_query' ) );
		$this->getContainer()->share( 'rucss_frontend_subscriber', 'WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber' )
			->withArgument( $this->getContainer()->get( 'rucss_used_css_controller' ) )
			->withArgument( $this->getContainer()->get( 'rucss_api_client' ) );
	}
}
