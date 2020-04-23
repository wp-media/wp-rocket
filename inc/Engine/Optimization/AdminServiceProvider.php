<?php
namespace WP_Rocket\Engine\Optimization;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket optimizations
 *
 * @since 3.5.3
 */
class AdminServiceProvider extends AbstractServiceProvider {

	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'minify_css_admin_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @since 3.5.3
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'minify_css_admin_subscriber', 'WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber' );
	}
}
