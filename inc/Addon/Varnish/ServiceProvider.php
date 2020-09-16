<?php
namespace WP_Rocket\Addon\Varnish;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for Varnish Addon.
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
		'varnish',
		'varnish_subscriber',
	];

	/**
	 * Registers the subscribers in the container.
	 *
	 * @since 3.3
	 */
	public function register() {
		$this->getContainer()->add( 'varnish', 'WP_Rocket\Addon\Varnish\Varnish' );
		$this->getContainer()->share( 'varnish_subscriber', 'WP_Rocket\Addon\Varnish\Subscriber' )
			->withArgument( $this->getContainer()->get( 'varnish' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
	}
}
