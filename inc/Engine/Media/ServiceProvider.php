<?php
namespace WP_Rocket\Engine\Media;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for Media module
 *
 * @since 3.6
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
		'lazyload_assets',
		'lazyload_image',
		'lazyload_iframe',
		'lazyload_subscriber',
	];

	/**
	 * Registers the services in the container
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'lazyload_assets', 'RocketLazyload\Assets' );
		$this->getContainer()->add( 'lazyload_image', 'RocketLazyload\Image' );
		$this->getContainer()->add( 'lazyload_iframe', 'RocketLazyload\Iframe' );
		$this->getContainer()->share( 'lazyload_subscriber', 'WP_Rocket\Engine\Media\LazyloadSubscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( $this->getContainer()->get( 'lazyload_assets' ) )
			->withArgument( $this->getContainer()->get( 'lazyload_image' ) )
			->withArgument( $this->getContainer()->get( 'lazyload_iframe' ) );
	}
}
