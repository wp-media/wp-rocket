<?php

namespace WP_Rocket\Addon\Cloudflare\API;

class Endpoints {
	/**
	 * Get zone data.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function get_zones() {
		return $this->get( "zones/{$this->zone_id}" );
	}

	/**
	 * Get the zone's page rules.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function list_pagerules() {
		return $this->get( "zones/{$this->zone_id}/pagerules?status=active" );
	}

	/**
	 * Purges the cache.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function purge() {
		return $this->delete( "zones/{$this->zone_id}/purge_cache", [ 'purge_everything' => true ] );
	}

	/**
	 * Purges the given URLs.
	 *
	 * @since 1.0
	 *
	 * @param array|null $urls An array of URLs that should be removed from cache.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function purge_files( array $urls ) {
		return $this->delete( "zones/{$this->zone_id}/purge_cache", [ 'files' => $urls ] );
	}

	/**
	 * Changes the zone's browser cache TTL setting.
	 *
	 * @since 1.0
	 *
	 * @param string $value New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function change_browser_cache_ttl( $value ) {
		return $this->change_setting( 'browser_cache_ttl', $value );
	}

	/**
	 * Changes the zone's rocket loader setting.
	 *
	 * @since 1.0
	 *
	 * @param string $value New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function change_rocket_loader( $value ) {
		return $this->change_setting( 'rocket_loader', $value );
	}

	/**
	 * Changes the zone's minify setting.
	 *
	 * @since 1.0
	 *
	 * @param string $value New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function change_minify( $value ) {
		return $this->change_setting( 'minify', $value );
	}

	/**
	 * Changes the zone's cache level.
	 *
	 * @since 1.0
	 *
	 * @param string $value New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function change_cache_level( $value ) {
		return $this->change_setting( 'cache_level', $value );
	}

	/**
	 * Changes the zone's development mode.
	 *
	 * @since 1.0
	 *
	 * @param string $value New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function change_development_mode( $value ) {
		return $this->change_setting( 'development_mode', $value );
	}

	/**
	 * Changes the given setting.
	 *
	 * @since 1.0
	 *
	 * @param string $setting Name of the setting to change.
	 * @param string $value   New setting's value.
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	protected function change_setting( $setting, $value ) {
		return $this->patch( "zones/{$this->zone_id}/settings/{$setting}", [ 'value' => $value ] );
	}

	/**
	 * Gets all of the Cloudflare settings.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function get_settings() {
		return $this->get( "zones/{$this->zone_id}/settings" );
	}

	/**
	 * Gets Cloudflare's IPs.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Cloudflare response packet.
	 */
	public function get_ips() {
		return $this->get( '/ips' );
	}
}
