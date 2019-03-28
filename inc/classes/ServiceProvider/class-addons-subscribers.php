<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket addons
 *
 * @since 3.3
 * @author Remy Perona
 */
class Addons_Subscribers extends AbstractServiceProvider {

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
		'busting_factory',
		'facebook_tracking_subscriber',
		'google_tracking_subscriber',
		'sucuri_subscriber',
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
		$this->getContainer()->add( 'busting_factory', 'WP_Rocket\Busting\Busting_Factory' )
			->withArgument( WP_ROCKET_CACHE_BUSTING_PATH )
			->withArgument( WP_ROCKET_CACHE_BUSTING_URL );
		$this->getContainer()->add( 'facebook_tracking_subscriber', 'WP_Rocket\Subscriber\Facebook_Tracking_Cache_Busting_Subscriber' )
			->withArgument( $this->getContainer()->get( 'busting_factory' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'google_tracking_subscriber', 'WP_Rocket\Subscriber\Google_Tracking_Cache_Busting_Subscriber' )
			->withArgument( $this->getContainer()->get( 'busting_factory' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'sucuri_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Security\Sucuri_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );

	}
}
