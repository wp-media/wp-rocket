<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\License\API\PricingClient;

/**
 * Service Provider for the License module
 *
 * @since 3.7.3
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Aliases the service provider provides
	 *
	 * @var array
	 */
	protected $provides = [
		'pricing_client',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'pricing_client', PricingClient::class );
	}
}
