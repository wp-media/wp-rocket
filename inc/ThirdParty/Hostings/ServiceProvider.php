<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\BootableServiceProviderInterface;
use WP_Rocket\ThirdParty\Hostings\HostResolver;
use WP_Rocket\ThirdParty\Hostings\HostSubscriberFactory;

/**
 * Hostings compatibility service provider
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [];

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
	 * Register the service in the provider array
	 *
	 * @return void
	 */
	public function boot(): void {
		$hosting_service = HostResolver::get_host_service();

		if ( ! empty( $hosting_service ) ) {
			$this->provides[] = $hosting_service;
		}
	}

	/**
	 * Registers the current hosting subscriber in the container
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function register(): void {
		$hosting_service = HostResolver::get_host_service();

		if ( ! empty( $hosting_service ) ) {
			$this->getContainer()
				->addShared( $hosting_service, ( new HostSubscriberFactory() )->get_subscriber() );
		}
	}
}
