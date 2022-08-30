<?php
namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

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
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->share( 'cdn', CDN::class )
			->addArgument( $options );
		$this->getContainer()->share( 'cdn_subscriber', Subscriber::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cdn' ) )
			->addTag( 'common_subscriber' );
	}
}
