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
			$this->generate_container_id('lazyload_admin_subscriber'),
			$this->generate_container_id('image_dimensions_admin_subscriber'),
		];
	}

	public function get_license_subscribers(): array
	{
		return [
			$this->generate_container_id('emojis_subscriber'),
			$this->generate_container_id('image_dimensions_subscriber'),
		];
	}

	public function declare()
	{
		$this->register_service('lazyload_assets', function ($id) {
			$this->add( $id, Assets::class );
		});

		$this->register_service('lazyload_image', function ($id) {
			$this->add( $id, Image::class );
		});

		$this->register_service('lazyload_iframe', function ($id) {
			$this->add( $id, Iframe::class );
		});

		$this->register_service('lazyload_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->getContainer()->get( 'options' ) )
				->addArgument( $this->get_internal( 'lazyload_assets' ) )
				->addArgument( $this->get_internal( 'lazyload_image' ) )
				->addArgument( $this->get_internal( 'lazyload_iframe' ) )
				->addTag( 'lazyload_subscriber' );
		});

		$this->register_service('lazyload_admin_subscriber', function ($id) {
			$this->share( $id, LazyloadAdminSubscriber::class )
				->addTag( 'admin_subscriber' );
		});

		$this->register_service('emojis_subscriber', function ($id) {
			$this->share( $id, EmojisSubscriber::class )
				->addArgument( $this->getContainer()->get( 'options' ) )
				->addTag( 'front_subscriber' );
		});

		$this->register_service('image_dimensions', function ($id) {
			$this->add( $id, ImageDimensions::class )
				->addArgument( $this->getContainer()->get( 'options' ) );
		});

		$this->register_service('image_dimensions_subscriber', function ($id) {
			$this->share( $id, ImageDimensionsSubscriber::class )
				->addArgument( $this->get_internal( 'image_dimensions' ) )
				->addTag( 'front_subscriber' );
		});

		$this->register_service('image_dimensions_admin_subscriber', function ($id) {
			$this->share( $id, ImageDimensionsAdminSubscriber::class )
				->addArgument( $this->get_internal( 'image_dimensions' ) )
				->addTag( 'admin_subscriber' );
		});
	}
}
