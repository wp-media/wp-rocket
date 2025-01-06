<?php
declare(strict_types=1);

namespace WP_Rocket\Addon;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Addon\Sucuri\Subscriber as SucuriSubscriber;
use WP_Rocket\Addon\WebP\AdminSubscriber as WebPAdminSubscriber;
use WP_Rocket\Addon\WebP\Subscriber as WebPSubscriber;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Subscriber as CDNSubscriber;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket addons.
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'sucuri_subscriber',
		'webp_subscriber',
		'webp_admin_subscriber',
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
	 */
	public function register(): void {
		$this->getContainer()->addShared( 'sucuri_subscriber', SucuriSubscriber::class )
			->addArgument( Options_Data::class );

		$this->getContainer()->addShared( 'webp_admin_subscriber', WebPAdminSubscriber::class )
			->addArguments(
				[
					Options_Data::class,
					CDNSubscriber::class,
					Beacon::class,
				]
			);

		$this->getContainer()->addShared( 'webp_subscriber', WebPSubscriber::class )
			->addArguments(
				[
					Options_Data::class,
					Options::class,
					CDNSubscriber::class,
				]
			);
	}
}
