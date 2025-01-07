<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Admin;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider.
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'lazyload_css_admin_subscriber',
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

		$this->getContainer()->addShared( 'lazyload_css_admin_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'lazyload_css_cache' ) );
	}
}
