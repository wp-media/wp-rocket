<?php
namespace WP_Rocket\Engine\Media;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

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
		'emojis_subscriber',
		'image_dimensions',
		'image_dimensions_subscriber',
		'image_dimensions_admin_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'lazyload_assets', 'WP_Rocket\Dependencies\RocketLazyload\Assets' );
		$this->getContainer()->add( 'lazyload_image', 'WP_Rocket\Dependencies\RocketLazyload\Image' );
		$this->getContainer()->add( 'lazyload_iframe', 'WP_Rocket\Dependencies\RocketLazyload\Iframe' );
		$this->getContainer()->share( 'lazyload_subscriber', 'WP_Rocket\Engine\Media\Lazyload\Subscriber' )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'lazyload_assets' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_image' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_iframe' ) )
			->addTag( 'lazyload_subscriber' );
		$this->getContainer()->share( 'lazyload_admin_subscriber', 'WP_Rocket\Engine\Media\Lazyload\AdminSubscriber' )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->share( 'emojis_subscriber', 'WP_Rocket\Engine\Media\Emojis\EmojisSubscriber' )
			->addArgument( $options )
			->addTag( 'front_subscriber' );
		$this->getContainer()->add( 'image_dimensions', 'WP_Rocket\Engine\Media\ImageDimensions\ImageDimensions' )
			->addArgument( $options );
		$this->getContainer()->share( 'image_dimensions_subscriber', 'WP_Rocket\Engine\Media\ImageDimensions\Subscriber' )
			->addArgument( $this->getContainer()->get( 'image_dimensions' ) )
			->addTag( 'front_subscriber' );
		$this->getContainer()->share( 'image_dimensions_admin_subscriber', 'WP_Rocket\Engine\Media\ImageDimensions\AdminSubscriber' )
			->addArgument( $this->getContainer()->get( 'image_dimensions' ) )
			->addTag( 'admin_subscriber' );
	}
}
