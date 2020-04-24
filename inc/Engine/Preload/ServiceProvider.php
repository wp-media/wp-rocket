<?php
namespace WP_Rocket\Engine\Preload;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket preload.
 *
 * @since 3.3
 * @author Remy Perona
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
		'full_preload_process',
		'partial_preload_process',
		'homepage_preload',
		'sitemap_preload',
		'preload_subscriber',
		'sitemap_preload_subscriber',
		'partial_preload_subscriber',
		'fonts_preload_subscriber',
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
		$this->getContainer()->add( 'full_preload_process', 'WP_Rocket\Engine\Preload\FullProcess' );
		$this->getContainer()->add( 'partial_preload_process', 'WP_Rocket\Engine\Preload\PartialProcess' );

		$full_preload_process = $this->getContainer()->get( 'full_preload_process' );
		$this->getContainer()->add( 'homepage_preload', 'WP_Rocket\Engine\Preload\Homepage' )
			->withArgument( $full_preload_process );
		$this->getContainer()->add( 'sitemap_preload', 'WP_Rocket\Engine\Preload\Sitemap' )
			->withArgument( $full_preload_process );

		// Subscribers.
		$options = $this->getContainer()->get( 'options' );
		$this->getContainer()->share( 'preload_subscriber', 'WP_Rocket\Engine\Preload\PreloadSubscriber' )
			->withArgument( $this->getContainer()->get( 'homepage_preload' ) )
			->withArgument( $options );
		$this->getContainer()->share( 'sitemap_preload_subscriber', 'WP_Rocket\Engine\Preload\SitemapPreloadSubscriber' )
			->withArgument( $this->getContainer()->get( 'sitemap_preload' ) )
			->withArgument( $options );
		$this->getContainer()->share( 'partial_preload_subscriber', 'WP_Rocket\Engine\Preload\PartialPreloadSubscriber' )
			->withArgument( $this->getContainer()->get( 'partial_preload_process' ) )
			->withArgument( $options );
		$this->getContainer()->share( 'fonts_preload_subscriber', 'WP_Rocket\Engine\Preload\Fonts' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'cdn' ) );
	}
}
