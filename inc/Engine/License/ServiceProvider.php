<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\AbstractServiceProvider;
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

	public function get_admin_subscribers(): array
	{
		return [
			$this->getInternal('license_subscriber')
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$views = __DIR__ . '/views';

		$this->add( 'pricing_client', PricingClient::class );
		$this->add( 'user_client', UserClient::class )
			->addArgument( $this->getInternal( 'options' ) );
		$this->share( 'pricing', Pricing::class )
			->addArgument( $this->getInternal( 'pricing_client' )->get_pricing_data() );
		$this->share( 'user', User::class )
			->addArgument( $this->getInternal( 'user_client' )->get_user_data() );
		$this->add( 'upgrade', Upgrade::class )
			->addArgument( $this->getInternal( 'pricing' ) )
			->addArgument( $this->getInternal( 'user' ) )
			->addArgument( $views );
		$this->add( 'renewal', Renewal::class )
			->addArgument( $this->getInternal( 'pricing' ) )
			->addArgument( $this->getInternal( 'user' ) )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $views );
		$this->share( 'license_subscriber', Subscriber::class )
			->addArgument( $this->getInternal( 'upgrade' ) )
			->addArgument( $this->getInternal( 'renewal' ) )
			->addTag( 'admin_subscriber' );
	}
}
