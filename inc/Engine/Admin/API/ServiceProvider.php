<?php

namespace WP_Rocket\Engine\Admin\API;

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
		'admin_api_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'admin_api_subscriber', Subscriber::class );
	}
}
