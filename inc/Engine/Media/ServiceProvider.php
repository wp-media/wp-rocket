<?php
namespace WP_Rocket\Engine\Media;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

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
		'lazyload_admin_subscriber',
		'embeds_subscriber',
		'emojis_subscriber',
		'image_dimensions',
		'image_dimensions_subscriber',
		'image_dimensions_admin_subscriber',
	];

	/**
	 * Registers the services in the container
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'lazyload_assets', 'WP_Rocket\Dependencies\RocketLazyload\Assets' );
		$this->getContainer()->add( 'lazyload_image', 'WP_Rocket\Dependencies\RocketLazyload\Image' );
		$this->getContainer()->add( 'lazyload_iframe', 'WP_Rocket\Dependencies\RocketLazyload\Iframe' );
		$this->getContainer()->share( 'lazyload_subscriber', 'WP_Rocket\Engine\Media\Lazyload\Subscriber' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'lazyload_assets' ) )
			->withArgument( $this->getContainer()->get( 'lazyload_image' ) )
			->withArgument( $this->getContainer()->get( 'lazyload_iframe' ) );
		$this->getContainer()->share( 'lazyload_admin_subscriber', 'WP_Rocket\Engine\Media\Lazyload\AdminSubscriber' );
		$this->getContainer()->share( 'embeds_subscriber', 'WP_Rocket\Engine\Media\Embeds\EmbedsSubscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'emojis_subscriber', 'WP_Rocket\Engine\Media\Emojis\EmojisSubscriber' )
			->withArgument( $options );
		$this->getContainer()->add( 'image_dimensions', 'WP_Rocket\Engine\Media\ImageDimensions\ImageDimensions' )
			->withArgument( $options );
		$this->getContainer()->share( 'image_dimensions_subscriber', 'WP_Rocket\Engine\Media\ImageDimensions\Subscriber' )
			->withArgument( $this->getContainer()->get( 'image_dimensions' ) );
		$this->getContainer()->share( 'image_dimensions_admin_subscriber', 'WP_Rocket\Engine\Media\ImageDimensions\AdminSubscriber' )
			->withArgument( $this->getContainer()->get( 'image_dimensions' ) );
	}
}
