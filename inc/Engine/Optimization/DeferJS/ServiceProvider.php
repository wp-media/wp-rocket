<?php
namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket Defer JS
 *
 * @since 3.8
 */
class ServiceProvider extends AbstractServiceProvider {

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
		'defer_js',
		'defer_js_admin_subscriber',
		'defer_js_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'defer_js', 'WP_Rocket\Engine\Optimization\DeferJS\DeferJS' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'defer_js_admin_subscriber', 'WP_Rocket\Engine\Optimization\DeferJS\AdminSubscriber' )
			->withArgument( $this->getContainer()->get( 'defer_js' ) );
		$this->getContainer()->share( 'defer_js_subscriber', 'WP_Rocket\Engine\Optimization\DeferJS\Subscriber' )
			->withArgument( $this->getContainer()->get( 'defer_js' ) );
	}
}
