<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
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
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'pricing', Pricing::class )
			->addArgument( $this->getContainer()->get( 'pricing_client' )->get_pricing_data() );
		$this->getContainer()->share( 'user', User::class )
			->addArgument( $this->getContainer()->get( 'user_client' )->get_user_data() );
		$this->getContainer()->add( 'upgrade', Upgrade::class )
			->addArgument( $this->getContainer()->get( 'pricing' ) )
			->addArgument( $this->getContainer()->get( 'user' ) )
			->addArgument( $views );
		$this->getContainer()->add( 'renewal', Renewal::class )
			->addArgument( $this->getContainer()->get( 'pricing' ) )
			->addArgument( $this->getContainer()->get( 'user' ) )
			->addArgument( $views );
		$this->getContainer()->share( 'license_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'upgrade' ) )
			->addArgument( $this->getContainer()->get( 'renewal' ) )
			->addTag( 'admin_subscriber' );
	}
}
