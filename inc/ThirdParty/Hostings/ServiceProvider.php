<?php

namespace WP_Rocket\ThirdParty\Hostings;

use League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\ThirdParty\Hostings\HostResolver;
use WP_Rocket\ThirdParty\Hostings\HostSubscriberFactory;

/**
 * Hostings compatibility service provider
 *
 * @since 3.6.3
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Services provided
	 *
	 * @var array
	 */
	protected $provides = [];

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
			$host_subscriber = ( new HostSubscriberFactory() )->get_subscriber();

			$this->provides[] = $hosting_service;

			$this->getContainer()->share( $hosting_service, $host_subscriber );
		}
	}
}
