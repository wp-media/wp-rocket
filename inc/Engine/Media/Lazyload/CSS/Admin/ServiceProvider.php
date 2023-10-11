<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Admin;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider.
 */
class ServiceProvider extends AbstractServiceProvider {


	/**
	 * The provided array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'lazyload_css_admin_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {

		$this->getContainer()->share( 'lazyload_css_admin_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'lazyload_css_cache' ) );
	}
}
