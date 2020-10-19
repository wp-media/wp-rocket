<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\License\API\PricingClient;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Engine\License\Subscriber;
use WP_Rocket\Engine\License\Upgrade;

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
		'upgrade',
		'renewal',
		'license_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$views = __DIR__ . '/views';

		$this->getContainer()->add( 'pricing_client', PricingClient::class );
		$this->getContainer()->add( 'user_client', UserClient::class )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'pricing', Pricing::class )
			->withArgument( $this->getContainer()->get( 'pricing_client' )->get_pricing_data() );
		$this->getContainer()->share( 'user', User::class )
			->withArgument( $this->getContainer()->get( 'user_client' )->get_user_data() );
		$this->getContainer()->add( 'upgrade', Upgrade::class )
			->withArgument( $this->getContainer()->get( 'pricing' ) )
			->withArgument( $this->getContainer()->get( 'user' ) )
			->withArgument( $views );
		$this->getContainer()->add( 'renewal', Renewal::class )
			->withArgument( $this->getContainer()->get( 'pricing' ) )
			->withArgument( $this->getContainer()->get( 'user' ) )
			->withArgument( $views );
		$this->getContainer()->share( 'license_subscriber', Subscriber::class )
			->withArgument( $this->getContainer()->get( 'upgrade' ) )
			->withArgument( $this->getContainer()->get( 'renewal' ) );
	}
}
