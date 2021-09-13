<?php
namespace WP_Rocket\Engine\Admin\Database;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for database optimization
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
		'db_optimization_process',
		'db_optimization',
		'db_optimization_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'db_optimization_process', 'WP_Rocket\Engine\Admin\Database\OptimizationProcess' );
		$this->getContainer()->add( 'db_optimization', 'WP_Rocket\Engine\Admin\Database\Optimization' )
			->addArgument( $this->getContainer()->get( 'db_optimization_process' ) );
		$this->getContainer()->share( 'db_optimization_subscriber', 'WP_Rocket\Engine\Admin\Database\Subscriber' )
			->addArgument( $this->getContainer()->get( 'db_optimization' ) )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'common_subscriber' );
	}
}
