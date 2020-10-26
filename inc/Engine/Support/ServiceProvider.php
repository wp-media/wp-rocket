<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

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
		'support_subscriber',
	];

	/**
	 * Registers the services in the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'support_data', 'WP_Rocket\Engine\Support\Data' )
			->withArgument( $options );
		$this->getContainer()->add( 'rest_support', 'WP_Rocket\Engine\Support\Rest' )
			->withArgument( $this->getContainer()->get( 'support_data' ) )
			->withArgument( $options );
		$this->getContainer()->share( 'support_subscriber', 'WP_Rocket\Engine\Support\Subscriber' )
			->withArgument( $this->getContainer()->get( 'rest_support' ) );
	}
}
