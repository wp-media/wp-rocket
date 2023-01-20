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

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('license_subscriber')
		];
	}

	public function declare()
	{
		$views = __DIR__ . '/views';

		$this->register_service('pricing_client', function ($id) {
			$this->add( $id, PricingClient::class );
		});

		$this->register_service('user_client', function ($id) {
			$this->add( $id, UserClient::class )
				->addArgument( $this->get_external( 'options' ) );
		});

		$this->register_service('pricing', function ($id) {
			$this->share( $id, Pricing::class )
				->addArgument( $this->get_internal( 'pricing_client' )->get_pricing_data() );
		});

		$this->register_service('user', function ($id) {
			$this->share( $id, User::class )
				->addArgument( $this->get_internal( 'user_client' )->get_user_data() );
		});

		$this->register_service('upgrade', function ($id) use ($views) {
			$this->add( $id, Upgrade::class )
				->addArgument( $this->get_internal( 'pricing' ) )
				->addArgument( $this->get_internal( 'user' ) )
				->addArgument( $views );
		});

		$this->register_service('renewal', function ($id) use ($views) {
			$this->add( $id, Renewal::class )
				->addArgument( $this->get_internal( 'pricing' ) )
				->addArgument( $this->get_internal( 'user' ) )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $views );
		});

		$this->register_service('license_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'upgrade' ) )
				->addArgument( $this->get_internal( 'renewal' ) )
				->addTag( 'admin_subscriber' );
		});
	}
}
