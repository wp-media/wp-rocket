<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Admin;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for RocketCDN
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'cdn_admin_settings',
		'cdn_admin_subscriber',
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
		$this->getContainer()->addShared( 'cdn_admin_settings', Settings::class )
			->addArguments(
				[
					'options',
					'options_api',
					'rocketcdn_subscription_controller',
				]
			);

		$this->getContainer()->addShared( 'cdn_admin_subscriber', Subscriber::class )
			->addArgument( 'cdn_admin_settings' );
	}
}
