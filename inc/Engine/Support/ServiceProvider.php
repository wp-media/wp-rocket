<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
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
			->addArgument( $options );
		$this->getContainer()->add( 'rest_support', Rest::class )
			->addArgument( $this->getContainer()->get( 'support_data' ) )
			->addArgument( $options );
		$this->getContainer()->share( 'support_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'rest_support' ) )
			->addTag( 'common_subscriber' );
	}
}
