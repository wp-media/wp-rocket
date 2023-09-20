<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare\API;

use WP_Rocket\Addon\Cloudflare\Auth\AuthInterface;

class Endpoints {
	/**
	 * Client instance
	 *
	 * @var Client
	 */
	private $client;

	/**
	 * Constructor
	 *
	 * @param Client $client Client instance.
	 */
	public function __construct( Client $client ) {
		$this->client = $client;
	}

	/**
	 * Get zone data.
	 *
	 * @param string $zone_id Zone ID.
	 *
	 * @return object
	 */
	public function get_zones( string $zone_id ) {
		return $this->client->get( "zones/{$zone_id}" );
	}

	/**
	 * Get the zone's page rules.
	 *
	 * @param string $zone_id Zone ID.
	 * @param string $status Rule status.
	 *
	 * @return object
	 */
	public function list_pagerules( string $zone_id, string $status ) {
		return $this->client->get( "zones/{$zone_id}/pagerules?status={$status}" );
	}

	/**
	 * Purges the cache.
	 *
	 * @param string $zone_id Zone ID.
	 *
	 * @return object
	 */
	public function purge( string $zone_id ) {
		return $this->client->post( "zones/{$zone_id}/purge_cache", [ 'purge_everything' => true ] );
	}

	/**
	 * Purges the given URLs.
	 *
	 * @param string $zone_id Zone ID.
	 * @param array  $urls An array of URLs that should be removed from cache.
	 *
	 * @return object
	 */
	public function purge_files( string $zone_id, array $urls = [] ) {
		return $this->client->post( "zones/{$zone_id}/purge_cache", [ 'files' => $urls ] );
	}

	/**
	 * Updates the zone's browser cache TTL setting
	 *
	 * @param string $zone_id Zone ID.
	 * @param string $value Cache TTL value.
	 *
	 * @return object
	 */
	public function update_browser_cache_ttl( string $zone_id, $value ) {
		return $this->update_setting( $zone_id, 'browser_cache_ttl', $value );
	}

	/**
	 * Updates the zone's rocket loader setting.
	 *
	 * @param string $zone_id Zone ID.
	 * @param string $value Rocket Loader value.
	 *
	 * @return object
	 */
	public function update_rocket_loader( string $zone_id, $value ) {
		return $this->update_setting( $zone_id, 'rocket_loader', $value );
	}

	/**
	 * Updates the zone's minify setting.
	 *
	 * @param string   $zone_id Zone ID.
	 * @param string[] $value Minify value.
	 *
	 * @return object
	 */
	public function update_minify( string $zone_id, $value ) {
		return $this->update_setting( $zone_id, 'minify', $value );
	}

	/**
	 * Updates the zone's cache level.
	 *
	 * @param string $zone_id Zone ID.
	 * @param string $value Cache level value.
	 *
	 * @return object
	 */
	public function change_cache_level( string $zone_id, $value ) {
		return $this->update_setting( $zone_id, 'cache_level', $value );
	}

	/**
	 * Changes the zone's development mode.
	 *
	 * @param string $zone_id Zone ID.
	 * @param string $value Development mode value.
	 *
	 * @return object
	 */
	public function change_development_mode( string $zone_id, $value ) {
		return $this->update_setting( $zone_id, 'development_mode', $value );
	}

	/**
	 * Updates the given setting.
	 *
	 * @param string $zone_id Zone ID.
	 * @param string $setting Name of the setting to change.
	 * @param mixed  $value   Setting value.
	 *
	 * @return object
	 */
	protected function update_setting( string $zone_id, $setting, $value ) {
		return $this->client->patch( "zones/{$zone_id}/settings/{$setting}", [ 'value' => $value ] );
	}

	/**
	 * Gets all of the Cloudflare settings.
	 *
	 * @param string $zone_id Zone ID.
	 *
	 * @return object
	 */
	public function get_settings( string $zone_id ) {
		return $this->client->get( "zones/{$zone_id}/settings" );
	}

	/**
	 * Gets Cloudflare's IPs.
	 *
	 * @return object
	 */
	public function get_ips() {
		return $this->client->get( '/ips' );
	}

	/**
	 * Change client auth.
	 *
	 * @param AuthInterface $auth Client auth.
	 *
	 * @return void
	 */
	public function change_auth( AuthInterface $auth ) {
		$this->client->set_auth( $auth );
	}
}
