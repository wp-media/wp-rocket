<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload\Links;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket preload links.
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'preload_links_admin_subscriber',
		'preload_links_subscriber',
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
	 * Registers the subscribers in the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->addShared( 'preload_links_admin_subscriber', AdminSubscriber::class )
			->addArgument( 'options' );
		$this->getContainer()->addShared( 'preload_links_subscriber', Subscriber::class )
			->addArgument( 'options' )
			->addArgument( rocket_direct_filesystem() );
	}
}
