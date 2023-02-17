<?php

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket DynamicLists
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
		'dynamic_lists_data_manager',
		'dynamic_lists_api_client',
		'dynamic_lists',
		'dynamic_lists_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'dynamic_lists_data_manager', DataManager::class );
		$this->getContainer()->add( 'dynamic_lists_api_client', APIClient::class )
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'dynamic_lists', DynamicLists::class )
			->addArgument( $this->getContainer()->get( 'dynamic_lists_api_client' ) )
			->addArgument( $this->getContainer()->get( 'dynamic_lists_data_manager' ) )
			->addArgument( $this->getContainer()->get( 'user' ) )
			->addArgument( $this->getContainer()->get( 'template_path' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) );

		$this->getContainer()->share( 'dynamic_lists_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'dynamic_lists' ) );
	}
}
