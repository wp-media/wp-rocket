<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket preload
 *
 * @since 3.3
 * @author Remy Perona
 */
class Preload_Subscribers extends AbstractServiceProvider {

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
		$this->getContainer()->add( 'full_preload_process', 'WP_Rocket\Preload\Full_Process' );
		$this->getContainer()->add( 'partial_preload_process', 'WP_Rocket\Preload\Partial_Process' );
		$this->getContainer()->add( 'homepage_preload', 'WP_Rocket\Preload\Homepage' )
			->withArgument( $this->getContainer()->get( 'full_preload_process' ) );
		$this->getContainer()->add( 'sitemap_preload', 'WP_Rocket\Preload\Sitemap' )
			->withArgument( $this->getContainer()->get( 'full_preload_process' ) );
		$this->getContainer()->share( 'preload_subscriber', 'WP_Rocket\Subscriber\Preload\Preload_Subscriber' )
			->withArgument( $this->getContainer()->get( 'homepage_preload' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'sitemap_preload_subscriber', 'WP_Rocket\Subscriber\Preload\Sitemap_Preload_Subscriber' )
			->withArgument( $this->getContainer()->get( 'sitemap_preload' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'partial_preload_subscriber', 'WP_Rocket\Subscriber\Preload\Partial_Preload_Subscriber' )
			->withArgument( $this->getContainer()->get( 'partial_preload_process' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
	}
}
