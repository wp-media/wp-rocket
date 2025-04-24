<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'tracking',
		'tracking_subscriber',
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
		$this->getContainer()->add( 'tracking', Tracking::class )
			->addArgument( 'a36067b00a263cce0299cfd960e26ecf' );
		$this->getContainer()->addShared( 'tracking_subscriber', Subscriber::class )
			->addArguments(
				[
					'options',
					'tracking',
				]
			);
	}
}
