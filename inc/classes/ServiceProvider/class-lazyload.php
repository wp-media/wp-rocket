<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket lazyload
 *
 * @since 3.3
 * @author Remy Perona
 */
class Lazyload extends AbstractServiceProvider {

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
		'lazyload_assets',
		'lazyload_image',
		'lazyload_iframe',
		'lazyload_subscriber',
	];

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'lazyload_assets', 'RocketLazyload\Assets' );
		$this->getContainer()->add( 'lazyload_image', 'RocketLazyload\Image' );
		$this->getContainer()->add( 'lazyload_iframe', 'RocketLazyload\Iframe' );
		$this->getContainer()->share( 'lazyload_subscriber', 'WP_Rocket\Subscriber\Optimization\Lazyload_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( $this->getContainer()->get( 'lazyload_assets' ) )
			->withArgument( $this->getContainer()->get( 'lazyload_image' ) )
			->withArgument( $this->getContainer()->get( 'lazyload_iframe' ) );
	}
}
