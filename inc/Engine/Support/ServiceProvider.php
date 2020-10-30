<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Support\Data;
use WP_Rocket\Engine\Support\Rest;
use WP_Rocket\Engine\Support\Subscriber;

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
		'support_data',
		'rest_support',
		'support_subscriber',
	];

	/**
	 * Registers the services in the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'support_data', Data::class )
			->withArgument( $options );
		$this->getContainer()->add( 'rest_support', Rest::class )
			->withArgument( $this->getContainer()->get( 'support_data' ) )
			->withArgument( $options );
		$this->getContainer()->share( 'support_subscriber', Subscriber::class )
			->withArgument( $this->getContainer()->get( 'rest_support' ) );
	}
}
