<?php
namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

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
		'dynamic_lists_data_manager',
		'dynamic_lists',
		'dynamic_lists_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'dynamic_lists_data_manager', 'WP_Rocket\Engine\Optimization\DynamicLists\DataManager' );
		$this->getContainer()->add( 'dynamic_lists', 'WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists' )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'dynamic_lists_data_manager' ) );
		$this->getContainer()->share( 'dynamic_lists_subscriber', 'WP_Rocket\Engine\Optimization\DynamicLists\Subscriber' )
			->addArgument( $this->getContainer()->get( 'dynamic_lists' ) );
	}
}
