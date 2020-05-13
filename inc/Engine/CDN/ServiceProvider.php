<?php
namespace WP_Rocket\Engine\CDN;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket CDN
 *
 * @since 3.5.5
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
		'cdn',
		'cdn_subscriber',
	];

	/**
	 * Registers the services in the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->share( 'cdn', 'WP_Rocket\Engine\CDN\CDN' )
			->withArgument( $options );
		$this->getContainer()->share( 'cdn_subscriber', 'WP_Rocket\Engine\CDN\Subscriber' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'cdn' ) );
	}
}
