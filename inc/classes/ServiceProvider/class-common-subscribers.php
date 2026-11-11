<?php
declare(strict_types=1);

namespace WP_Rocket\ServiceProvider;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Subscriber\Tools\Detect_Missing_Tags_Subscriber;

/**
 * Service provider for WP Rocket features common for admin and front
 */
class Common_Subscribers extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'detect_missing_tags_subscriber',
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
		$this->getContainer()->addShared( 'detect_missing_tags_subscriber', Detect_Missing_Tags_Subscriber::class );
	}
}
