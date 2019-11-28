<?php
namespace WP_Rocket\Addons\Cloudflare;

use Cloudflare\Api;
use Cloudflare\Zone\Cache;
use Cloudflare\Zone\PageRules;
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
	 * Cloudflare API Class
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @var Cloudflare\Api
	 */
	protected $api;

	/**
	 * Cloudflare Cache Class
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @var Cloudflare\Zone\Cache
	 */
	protected $cache;

	/**
	 * Cloudflare PageRules Class
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @var Cloudflare\Zone\PageRules
	 */
	protected $page_rules;

	/**
	 * Cloudflare Settings Class
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @var Cloudflare\Zone\Settings
	 */
	protected $settings;

	/**
	 * Cloudflare IPs Class
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @var Cloudflare\IPs
	 */
	protected $ips;

	/**
	 * Cloudflare Zone ID
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @var String
	 */
	protected $zone_id;

	/**
	 * Constructor
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @param Cloudflare\Api            $api        - Cloudflare API Instance.
	 * @param Cloudflare\Zone\Cache     $cache      - Cloudflare Cache Instance.
	 * @param Cloudflare\Zone\PageRules $page_rules - Cloudflare Page Rules Instance.
	 * @param Cloudflare\Zone\Settings  $settings   - Cloudflare Settings Instance.
	 * @param Cloudflare\IPs            $ips        - Cloudflare IPS Instance.
	 */
	public function __construct( $api, $cache, $page_rules, $settings, $ips ) {
		$this->api        = $api;
		$this->cache      = $cache;
		$this->page_rules = $page_rules;
		$this->settings   = $settings;
		$this->ips        = $ips;
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
	 * @param string $user_agent - Cloudflare User Agent.
	 * @return void
	 */
	public function set_api_credentials( $email, $api_key, $zone_id, $user_agent ) {
		$this->api->setEmail( $email );
		$this->api->setAuthKey( $api_key );
		$this->api->setCurlOption( CURLOPT_USERAGENT, $user_agent );

		$this->zone_id = $zone_id;
		// Loading with Valid API Credentials.
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
	 * @return Object
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
