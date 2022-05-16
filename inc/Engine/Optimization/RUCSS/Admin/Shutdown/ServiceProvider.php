<?php
namespace WP_Rocket\Engine\Optimization\RUCSS\Admin\Shutdown;

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
		'rucss_shutdown',
		'rucss_shutdown_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'rucss_shutdown', 'WP_Rocket\Engine\Optimization\RUCSS\Admin\Shutdown\Shutdown' )
		     ->addArgument( $this->getContainer()->get( 'user' ) )
		     ->addArgument( $this->getContainer()->get( 'template_path' ) . '/rucss-shutdown' );

		$this->getContainer()->add( 'rucss_shutdown_subscriber', 'WP_Rocket\Engine\Optimization\RUCSS\Admin\Shutdown\Subscriber' )
		     ->addArgument( $this->getContainer()->get( 'rucss_shutdown' ) );
	}
}
