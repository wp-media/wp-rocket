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
			$this->generate_container_id('license_subscriber')
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
			->addArgument( $this->get_internal( 'options' ) );
		$this->share( 'pricing', Pricing::class )
			->addArgument( $this->get_internal( 'pricing_client' )->get_pricing_data() );
		$this->share( 'user', User::class )
			->addArgument( $this->get_internal( 'user_client' )->get_user_data() );
		$this->add( 'upgrade', Upgrade::class )
			->addArgument( $this->get_internal( 'pricing' ) )
			->addArgument( $this->get_internal( 'user' ) )
			->addArgument( $views );
		$this->add( 'renewal', Renewal::class )
			->addArgument( $this->get_internal( 'pricing' ) )
			->addArgument( $this->get_internal( 'user' ) )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $views );
		$this->share( 'license_subscriber', Subscriber::class )
			->addArgument( $this->get_internal( 'upgrade' ) )
			->addArgument( $this->get_internal( 'renewal' ) )
			->addTag( 'admin_subscriber' );
	}
}
