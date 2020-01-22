<?php
namespace WP_Rocket\Addons\Cloudflare;

use Cloudflare\Api;
use Cloudflare\Zone\Cache;
use Cloudflare\Zone\Pagerules;
use Cloudflare\Zone\Settings;
use Cloudflare\IPs;

/**
 * CloudflareFacade
 *
 * @since 3.5
 * @author Soponar Cristina
 */
class CloudflareFacade {

	/**
	 * Instance of Cloudflare API.
	 *
	 * @var Cloudflare\Api
	 */
	protected $api;

	/**
	 * Instance of Cloudflare Zone Cache.
	 *
	 * @var Cloudflare\Zone\Cache
	 */
	protected $cache;

	/**
	 * Instance of Cloudflare Zone PageRules.
	 *
	 * @var Cloudflare\Zone\Pagerules
	 */
	protected $page_rules;

	/**
	 * Instance of Cloudflare Zone Settings.
	 *
	 * @var Cloudflare\Zone\Settings
	 */
	protected $settings;

	/**
	 * Instance of Cloudflare IPs.
	 *
	 * @var Cloudflare\IPs
	 */
	protected $ips;

	/**
	 * The Cloudflare Zone ID.
	 *
	 * @var String
	 */
	protected $zone_id;

	/**
	 * Instantiate the facade.
	 *
	 * @param Api $api Instance of the Cloudflare API.
	 */
	public function __construct( Api $api ) {
		$this->api = $api;
	}

	/**
	 * Sets Cloudflare email, API key and User Agent.
	 *
	 * @since  3.5
	 * @author Soponar Cristina
	 *
	 * @param string $email      - Cloudflare Email.
	 * @param string $api_key    - Cloudflare API Key.
	 * @param string $zone_id    - Cloudflare Zone ID.
	 */
	public function set_api_credentials( $email, $api_key, $zone_id ) {
		$this->api->setEmail( $email );
		$this->api->setAuthKey( $api_key );
		$this->api->setCurlOption(
			CURLOPT_USERAGENT,
			'wp-rocket/' . rocket_get_constant( 'WP_ROCKET_VERSION', '3.5' )
		);

		$this->zone_id = $zone_id;

		// Loading with Valid API Credentials.
		$this->init_api_objects();
	}

	/**
	 * Initialize the API's objects, i.e. page rules, cache, settings, and IPs.
	 *
	 * @since 3.5
	 */
	protected function init_api_objects() {
		$this->page_rules = new Pagerules( $this->api );
		$this->cache      = new Cache( $this->api );
		$this->settings   = new Settings( $this->api );
		$this->ips        = new IPs( $this->api );
	}

	/**
	 * Returns Cloudflare data about Zone ID.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @return stdClass
	 */
	public function get_zones() {
		return $this->api->get( 'zones/' . $this->zone_id );
	}

	/**
	 * Returns Cloudflare Page Rules based on Zone ID.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @return Object
	 */
	public function list_pagerules() {
		return $this->page_rules->list_pagerules( $this->zone_id, 'active' );
	}

	/**
	 * Returns Cloudflare Purge result.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @return Object
	 */
	public function purge() {
		return $this->cache->purge( $this->zone_id, true );
	}

	/**
	 * Returns Cloudflare Purge Files result.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @param array $purge_urls - URLs cache files to remove.
	 * @return Object
	 */
	public function purge_files( $purge_urls ) {
		return $this->cache->purge_files( $this->zone_id, $purge_urls );
	}

	/**
	 * Returns Cloudflare change browser cache ttl result.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @param string $mode - Value for Cloudflare browser cache TTL.
	 * @return Object
	 */
	public function change_browser_cache_ttl( $mode ) {
		return $this->settings->change_browser_cache_ttl( $this->zone_id, $mode );
	}

	/**
	 * Returns Cloudflare change rocket loader result.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @param string $mode - Value for Cloudflare Rocket Loader.
	 * @return Object
	 */
	public function change_rocket_loader( $mode ) {
		return $this->settings->change_rocket_loader( $this->zone_id, $mode );
	}

	/**
	 * Returns Cloudflare change minify result.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @param array $cf_minify_settings - Value for Cloudflare minification.
	 * @return Object
	 */
	public function change_minify( $cf_minify_settings ) {
		return $this->settings->change_minify( $this->zone_id, $cf_minify_settings );
	}

	/**
	 * Returns Cloudflare change cache level result.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @param string $mode - Value for Cloudflare caching level.
	 * @return Object
	 */
	public function change_cache_level( $mode ) {
		return $this->settings->change_cache_level( $this->zone_id, $mode );
	}

	/**
	 * Returns Cloudflare change development mode result.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @param string $value - Value for Cloudflare development mode.
	 * @return Object
	 */
	public function change_development_mode( $value ) {
		return $this->settings->change_development_mode( $this->zone_id, $value );
	}

	/**
	 * Cloudflare get_settings result.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @return Object
	 */
	public function settings() {
		return $this->settings->settings( $this->zone_id );
	}

	/**
	 * Cloudflare ips result.
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @return Object
	 */
	public function ips() {
		return $this->ips->ips();
	}
}
