<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\License\API\PricingClient;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\License\API\User;

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
		'user_client',
		'pricing',
		'user',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'pricing_client', PricingClient::class );
		$this->getContainer()->add( 'user_client', UserClient::class )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'pricing', Pricing::class )
			->withArgument( $this->getContainer()->get( 'pricing_client' )->get_pricing_data() );
		$this->getContainer()->add( 'user', User::class )
			->withArgument( $this->getContainer()->get( 'user_client' )->get_user_data() );
	}
}
