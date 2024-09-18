<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Support\Data;
use WP_Rocket\Engine\Support\Rest;
use WP_Rocket\Engine\Support\Subscriber;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'support_data',
		'rest_support',
		'support_subscriber',
	];

	/**
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Registers the services in the container
	 *
	 * @return void
	 */
	public function register(): void {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'support_data', Data::class )
			->addArgument( $options );
		$this->getContainer()->add( 'rest_support', Rest::class )
			->addArgument( $this->getContainer()->get( 'support_data' ) )
			->addArgument( $options );
		$this->getContainer()->addShared( 'support_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'rest_support' ) );
	}
}
