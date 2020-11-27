<?php

namespace WP_Rocket\Engine\Htaccess;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for the License module
 *
 * @since 3.7.3
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Aliases the service provider provides
	 *
	 * @var array
	 */
	protected $provides = [
		'htaccess_admin_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'htaccess_admin_subscriber', 'WP_Rocket\Engine\Htaccess\AdminSubscriber' );
	}
}
