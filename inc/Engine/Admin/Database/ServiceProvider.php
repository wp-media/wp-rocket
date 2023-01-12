<?php
namespace WP_Rocket\Engine\Admin\Database;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service Provider for database optimization
 *
 * @since 3.3
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('db_optimization_subscriber')
		];
	}

	/**
	 * Registers the option array in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register() {
		$this->add( 'db_optimization_process', OptimizationProcess::class );
		$this->add( 'db_optimization', Optimization::class )
			->addArgument( $this->get_internal( 'db_optimization_process' ) );
		$this->share( 'db_optimization_subscriber', Subscriber::class )
			->addArgument( $this->get_internal( 'db_optimization' ) )
			->addArgument( $this->get_external( 'options' ) )
			->addTag( 'common_subscriber' );
	}
}
