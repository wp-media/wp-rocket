<?php
namespace WP_Rocket\Engine\Media;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Dependencies\RocketLazyload\Assets;
use WP_Rocket\Dependencies\RocketLazyload\Iframe;
use WP_Rocket\Dependencies\RocketLazyload\Image;
use WP_Rocket\Engine\Media\Emojis\EmojisSubscriber;
use WP_Rocket\Engine\Media\ImageDimensions\AdminSubscriber as ImageDimensionsAdminSubscriber;
use WP_Rocket\Engine\Media\ImageDimensions\ImageDimensions;
use WP_Rocket\Engine\Media\ImageDimensions\Subscriber as ImageDimensionsSubscriber;
use WP_Rocket\Engine\Media\Lazyload\AdminSubscriber as LazyloadAdminSubscriber;
use WP_Rocket\Engine\Media\Lazyload\Subscriber;

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

		$this->getContainer()->add( 'lazyload_assets', Assets::class );
		$this->getContainer()->add( 'lazyload_image', Image::class );
		$this->getContainer()->add( 'lazyload_iframe', Iframe::class );
		$this->getContainer()->share( 'lazyload_subscriber', Subscriber::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'lazyload_assets' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_image' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_iframe' ) )
			->addTag( 'lazyload_subscriber' );
		$this->getContainer()->share( 'lazyload_admin_subscriber', LazyloadAdminSubscriber::class )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->share( 'emojis_subscriber', EmojisSubscriber::class )
			->addArgument( $options )
			->addTag( 'front_subscriber' );
		$this->getContainer()->add( 'image_dimensions', ImageDimensions::class )
			->addArgument( $options );
		$this->getContainer()->share( 'image_dimensions_subscriber', ImageDimensionsSubscriber::class )
			->addArgument( $this->getContainer()->get( 'image_dimensions' ) )
			->addTag( 'front_subscriber' );
		$this->getContainer()->share( 'image_dimensions_admin_subscriber', ImageDimensionsAdminSubscriber::class )
			->addArgument( $this->getContainer()->get( 'image_dimensions' ) )
			->addTag( 'admin_subscriber' );
	}
}
