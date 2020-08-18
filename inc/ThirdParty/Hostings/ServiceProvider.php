<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Container\ServiceProvider\BootableServiceProviderInterface;
use WP_Rocket\ThirdParty\Hostings\HostResolver;
use WP_Rocket\ThirdParty\Hostings\HostSubscriberFactory;

/**
 * Hostings compatibility service provider
 *
 * @since 3.6.3
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	/**
	 * Services provided
	 *
	 * @var array
	 */
	protected $provides = [];

	/**
	 * Register the service in the provider array
	 *
	 * @return void
	 */
	public function boot() {
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
	public function register() {
		$hosting_service = HostResolver::get_host_service();

		if ( ! empty( $hosting_service ) ) {
			$this->getContainer()->share( $hosting_service, ( new HostSubscriberFactory() )->get_subscriber() );
		}
	}
}
