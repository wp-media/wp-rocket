<?php

/**
 * Get an URL with one of CNAMES added in options
 *
 * @since 2.1
 *
 * @param  string $url The URL to parse.
 * @param  array  $zone (default: array( 'all' )). Deprecated.
 * @return string
 */
function get_rocket_cdn_url( $url, $zone = [ 'all' ] ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$container = apply_filters( 'rocket_container', '' );
	$cdn       = $container->get( 'cdn' );

	return $cdn->rewrite_url( $url );
}

/**
 * Wrapper of get_rocket_cdn_url() and print result
 *
 * @since 2.1
 *
 * @param string $url The URL to parse.
 * @param array  $zone (default: array( 'all' )). Deprecated.
 */
function rocket_cdn_url( $url, $zone = [ 'all' ] ) {
	echo esc_url( get_rocket_cdn_url( $url, $zone ) );
}

/**
 * Get all CNAMES.
 *
 * @since 2.1
 * @since 3.0 Don't check for WP Rocket CDN option activated to be able to use the function on Hosting with CDN auto-enabled.
 *
 * @param  string $zone List of zones. Default is 'all'.
 * @return array        List of CNAMES
 */
function get_rocket_cdn_cnames( $zone = 'all' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$hosts  = [];
	$cnames = get_rocket_option( 'cdn_cnames', [] );

	if ( $cnames ) {
		$cnames_zone = get_rocket_option( 'cdn_zone', [] );
		$zone        = (array) $zone;

		foreach ( $cnames as $k => $_urls ) {
			if ( ! in_array( $cnames_zone[ $k ], $zone, true ) ) {
				continue;
			}

			$_urls = explode( ',', $_urls );
			$_urls = array_map( 'trim', $_urls );

			foreach ( $_urls as $url ) {
				$hosts[] = $url;
			}
		}
	}

	/**
	 * Filter all CNAMES.
	 *
	 * @since 2.7
	 *
	 * @param array $hosts List of CNAMES.
	 * @param array $zone  Array of CDN zones.
	 */
	$hosts = (array) apply_filters( 'rocket_cdn_cnames', $hosts, $zone );
	$hosts = array_filter( $hosts );
	$hosts = array_flip( array_flip( $hosts ) );
	$hosts = array_values( $hosts );

	return $hosts;
}

/**
 * Check if the current URL is for a live site (not local, not staging).
 *
 * @since 3.5
 * @author Remy Perona
 *
 * @return bool True if live, false otherwise.
 */
function rocket_is_live_site() {
	if ( rocket_get_constant( 'WP_ROCKET_DEBUG' ) ) {
		return true;
	}

	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	if ( ! $host ) {
		return false;
	}

	// Check for local development sites.
	$local_tlds = [
		'127.0.0.1',
		'localhost',
		'.local',
		'.test',
		'.docksal',
		'.docksal.site',
		'.dev.cc',
		'.lndo.site',
	];
	foreach ( $local_tlds as $local_tld ) {
		if ( $host === $local_tld ) {
			return false;
		}

		// Check the TLD.
		if ( substr( $host, -strlen( $local_tld ) ) === $local_tld ) {
			return false;
		}
	}

	// Check for staging sites.
	$staging = [
		'.wpengine.com',
		'.pantheonsite.io',
		'.flywheelsites.com',
		'.flywheelstaging.com',
		'.kinsta.com',
		'.kinsta.cloud',
		'.cloudwaysapps.com',
		'.azurewebsites.net',
		'.wpserveur.net',
		'-liquidwebsites.com',
		'.myftpupload.com',
		'.dream.press',
		'.sg-host.com',
		'.platformsh.site',
		'.wpstage.net',
	];
	foreach ( $staging as $partial_host ) {
		if ( strpos( $host, $partial_host ) ) {
			return false;
		}
	}

	return true;
}
