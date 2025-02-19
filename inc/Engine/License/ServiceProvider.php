<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\License;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\License\API\{PricingClient, Pricing, UserClient, User};
use WP_Rocket\Engine\License\{Renewal, Upgrade, Subscriber};

/**
 * Service Provider for the License module
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
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
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$views = new StringArgument( __DIR__ . '/views' );

		$this->getContainer()->add( 'pricing_client', PricingClient::class );
		$this->getContainer()->add( 'user_client', UserClient::class )
			->addArgument( 'options' );
		$this->getContainer()->addShared( 'pricing', Pricing::class )
			->addArgument( $this->getContainer()->get( 'pricing_client' )->get_pricing_data() );
		$this->getContainer()->addShared( 'user', User::class )
			->addArgument( $this->getContainer()->get( 'user_client' )->get_user_data() );
		$this->getContainer()->add( 'upgrade', Upgrade::class )
			->addArguments(
				[
					'pricing',
					'user',
					$views,
				]
			);
		$this->getContainer()->add( 'renewal', Renewal::class )
			->addArguments(
				[
					'pricing',
					'user',
					'options',
					$views,
				]
			);
		$this->getContainer()->addShared( 'license_subscriber', Subscriber::class )
			->addArguments(
				[
					'upgrade',
					'renewal',
				]
			);
	}
}
