<?php
namespace WP_Rocket\Engine\Media;

use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Dependencies\RocketLazyload\{Assets, Iframe, Image};
use WP_Rocket\Engine\Media\Emojis\EmojisSubscriber;
use WP_Rocket\Engine\Media\ImageDimensions\{
	AdminSubscriber as ImageDimensionsAdminSubscriber,
	ImageDimensions,
	Subscriber as ImageDimensionsSubscriber
};
use WP_Rocket\Engine\Media\Lazyload\{
	AdminSubscriber as LazyloadAdminSubscriber,
	Subscriber
};

/**
 * Service provider for Media module
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'config',
		'tests',
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
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'config', Config::class )
			->addArgument( [ 'config_dir_path' => rocket_get_constant( 'WP_ROCKET_CONFIG_PATH' ) ] );
		$this->getContainer()->add( 'tests', Tests::class )
			->addArgument( $this->getContainer()->get( 'config' ) );

		$this->getContainer()->add( 'lazyload_assets', Assets::class );
		$this->getContainer()->add( 'lazyload_image', Image::class );
		$this->getContainer()->add( 'lazyload_iframe', Iframe::class );
		$this->getContainer()->addShared( 'lazyload_subscriber', Subscriber::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'lazyload_assets' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_image' ) )
			->addArgument( $this->getContainer()->get( 'lazyload_iframe' ) );
		$this->getContainer()->addShared( 'lazyload_admin_subscriber', LazyloadAdminSubscriber::class );
		$this->getContainer()->addShared( 'emojis_subscriber', EmojisSubscriber::class )
			->addArgument( $options );
		$this->getContainer()->add( 'image_dimensions', ImageDimensions::class )
			->addArgument( $options );
		$this->getContainer()->addShared( 'image_dimensions_subscriber', ImageDimensionsSubscriber::class )
			->addArgument( $this->getContainer()->get( 'image_dimensions' ) )
			->addArgument( $this->getContainer()->get( 'tests' ) );
		$this->getContainer()->addShared( 'image_dimensions_admin_subscriber', ImageDimensionsAdminSubscriber::class )
			->addArgument( $this->getContainer()->get( 'image_dimensions' ) );
	}
}
