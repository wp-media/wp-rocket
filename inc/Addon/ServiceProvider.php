<?php
namespace WP_Rocket\Addon;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket addons.
 *
 * @since 3.3
 * @since 3.5 - renamed and moved into this module.
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
		'busting_factory',
		'facebook_tracking_subscriber',
		'google_tracking_subscriber',
		'sucuri_subscriber',
		'varnish',
		'varnish_subscriber',
	];

	/**
	 * Registers the subscribers in the container.
	 *
	 * @since 3.3
	 */
	public function register() {
		// Busting Factory.
		$this->getContainer()->add( 'busting_factory', 'WP_Rocket\Busting\Busting_Factory' )
			->withArgument( rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_PATH' ) )
			->withArgument( rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_URL' ) );

		// Facebook Tracking Subscriber.
		$this->getContainer()->share( 'facebook_tracking_subscriber', 'WP_Rocket\Subscriber\Facebook_Tracking_Cache_Busting_Subscriber' )
			->withArgument( $this->getContainer()->get( 'busting_factory' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );

		// Google Tracking Subscriber.
		$this->getContainer()->share( 'google_tracking_subscriber', 'WP_Rocket\Subscriber\Google_Tracking_Cache_Busting_Subscriber' )
			->withArgument( $this->getContainer()->get( 'busting_factory' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );

		// Sucuri Addon.
		$this->getContainer()->share( 'sucuri_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Security\Sucuri_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );

		// Varnish Addon.
		$this->getContainer()->add( 'varnish', 'WP_Rocket\Addons\Varnish\Varnish' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'varnish_subscriber', 'WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber' )
			->withArgument( $this->getContainer()->get( 'varnish' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );

		// Cloudflare Addon.
		$this->addon_cloudflare();
	}

	/**
	 * Adds Cloudflare Addon into the Container when the addon is enabled.
	 *
	 * @since 3.5
	 */
	protected function addon_cloudflare() {
		// If the addon is not enabled, delete the transient and bail out. Don't load the addon.
		if ( ! get_rocket_option( 'do_cloudflare' ) ) {
			delete_transient( 'rocket_cloudflare_is_api_keys_valid' );
			return;
		}

		$this->provides[] = 'cloudflare_subscriber';

		$this->getContainer()->add( 'cloudflare_api', 'WPMedia\Cloudflare\APIClient' )
			->withArgument( rocket_get_constant( 'WP_ROCKET_VERSION' ) );
		$this->getContainer()->add( 'cloudflare', 'WPMedia\Cloudflare\Cloudflare' )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( $this->getContainer()->get( 'cloudflare_api' ) );
		$this->getContainer()->share( 'cloudflare_subscriber', 'WPMedia\Cloudflare\Subscriber' )
			->withArgument( $this->getContainer()->get( 'cloudflare' ) )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( $this->getContainer()->get( 'options_api' ) );
	}
}
