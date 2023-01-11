<?php
namespace WP_Rocket\Engine\Media;

use WP_Rocket\AbstractServiceProvider;
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

	public function get_admin_subscribers(): array
	{
		return [
			$this->getInternal('lazyload_admin_subscriber'),
			$this->getInternal('image_dimensions_admin_subscriber'),
		];
	}

	public function get_front_subscribers(): array
	{
		return [
			$this->getInternal('emojis_subscriber'),
			$this->getInternal('image_dimensions_subscriber'),
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->add( 'lazyload_assets', Assets::class );
		$this->add( 'lazyload_image', Image::class );
		$this->add( 'lazyload_iframe', Iframe::class );
		$this->share( 'lazyload_subscriber', Subscriber::class )
			->addArgument( $options )
			->addArgument( $this->getInternal( 'lazyload_assets' ) )
			->addArgument( $this->getInternal( 'lazyload_image' ) )
			->addArgument( $this->getInternal( 'lazyload_iframe' ) )
			->addTag( 'lazyload_subscriber' );
		$this->share( 'lazyload_admin_subscriber', LazyloadAdminSubscriber::class )
			->addTag( 'admin_subscriber' );
		$this->share( 'emojis_subscriber', EmojisSubscriber::class )
			->addArgument( $options )
			->addTag( 'front_subscriber' );
		$this->add( 'image_dimensions', ImageDimensions::class )
			->addArgument( $options );
		$this->share( 'image_dimensions_subscriber', ImageDimensionsSubscriber::class )
			->addArgument( $this->getInternal( 'image_dimensions' ) )
			->addTag( 'front_subscriber' );
		$this->share( 'image_dimensions_admin_subscriber', ImageDimensionsAdminSubscriber::class )
			->addArgument( $this->getInternal( 'image_dimensions' ) )
			->addTag( 'admin_subscriber' );
	}
}
