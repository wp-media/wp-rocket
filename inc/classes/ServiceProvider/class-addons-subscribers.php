<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

use \Cloudflare\Api as CloudflareApi;
use \Cloudflare\Zone\Cache as CloudflareCache;
use \Cloudflare\Zone\PageRules as CloudflarePageRules;
use \Cloudflare\Zone\Settings as CloudflareSettings;
use \Cloudflare\IPs as CloudflareIPs;

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
		'varnish',
		'varnish_subscriber',
		'cloudflare_subscriber',
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
		$this->getContainer()->share( 'facebook_tracking_subscriber', 'WP_Rocket\Subscriber\Facebook_Tracking_Cache_Busting_Subscriber' )
			->withArgument( $this->getContainer()->get( 'busting_factory' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'google_tracking_subscriber', 'WP_Rocket\Subscriber\Google_Tracking_Cache_Busting_Subscriber' )
			->withArgument( $this->getContainer()->get( 'busting_factory' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'sucuri_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Security\Sucuri_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'varnish', 'WP_Rocket\Addons\Varnish\Varnish' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'varnish_subscriber', 'WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber' )
			->withArgument( $this->getContainer()->get( 'varnish' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'cloudflare_facade', 'WP_Rocket\Addons\Cloudflare\CloudflareFacade' )
			->withArgument( new CloudflareApi() )
			->withArgument( new CloudflareCache() )
			->withArgument( new CloudflarePageRules() )
			->withArgument( new CloudflareSettings() )
			->withArgument( new CloudflareIPs() );
		$this->getContainer()->add( 'cloudflare', 'WP_Rocket\Addons\Cloudflare\Cloudflare' )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( $this->getContainer()->get( 'cloudflare_facade' ) );
		$this->getContainer()->share( 'cloudflare_subscriber', 'WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber' )
			->withArgument( $this->getContainer()->get( 'cloudflare' ) )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( $this->getContainer()->get( 'options_api' ) );
	}
}
