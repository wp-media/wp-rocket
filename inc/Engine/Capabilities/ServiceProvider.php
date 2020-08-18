<?php
namespace WP_Rocket\Engine\Capabilities;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for capabilities
 *
 * @since 3.6.3
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
		'capabilities_manager',
		'capabilities_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'capabilities_manager', 'WP_Rocket\Engine\Capabilities\Manager' );
		$this->getContainer()->share( 'capabilities_subscriber', 'WP_Rocket\Engine\Capabilities\Subscriber' )
			->withArgument( $this->getContainer()->get( 'capabilities_manager' ) );
	}
}
