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

	public function declare()
	{
		$this->register_service('db_optimization_process', function ($id) {
			$this->add( $id, OptimizationProcess::class );
		});

		$this->register_service('db_optimization', function ($id) {
			$this->add( $id, Optimization::class )
				->addArgument( $this->get_internal( 'db_optimization_process' ) );
		});

		$this->register_service('db_optimization_subscriber', function($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'db_optimization' ) )
				->addArgument( $this->get_external( 'options' ) )
				->addTag( 'common_subscriber' );
		});
	}
}
