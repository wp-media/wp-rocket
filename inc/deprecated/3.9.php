<?php

defined( 'ABSPATH' ) || exit;

class_alias( '\WP_Rocket\ThirdParty\Hostings\LiteSpeed', '\WP_Rocket\Subscriber\Third_Party\Hostings\Litespeed_Subscriber');

/**
 * Changes the text on the Varnish one-click block.
 *
 * @since 3.9.1 deprecated
 * @since 3.0
 * @author Remy Perona
 *
 * @param array $settings Field settings data.
 *
 * @return array modified field settings data.
 */
function rocket_godaddy_varnish_field( $settings ) {
	_deprecated_function( __FUNCTION__ . '()', '3.9.1', '\WP_Rocket\ThirdParty\Hostings\Godaddy::godaddy_varnish_field' );
	$settings['varnish_auto_purge']['title'] = sprintf(
	// Translators: %s = Hosting name.
		__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
		'GoDaddy'
	);

	return $settings;
}

/**
 * Remove expiration on HTML to prevent issue with Varnish cache.
 *
 * @since 3.9.1 deprecated
 * @since 2.9.5
 * @author Remy Perona
 *
 * @param  string $rules htaccess rules.
 * @return string        Updated htaccess rules.
 */
function rocket_remove_html_expire_goddady( $rules ) {
	_deprecated_function( __FUNCTION__ . '()', '3.9.1', '\WP_Rocket\ThirdParty\Hostings\Godaddy::remove_html_expire_goddady' );
	$rules = preg_replace( '@\s*#\s*Your document html@', '', $rules );
	$rules = preg_replace( '@\s*ExpiresByType text/html\s*"access plus \d+ (seconds|minutes|hour|week|month|year)"@', '', $rules );

	return $rules;
}

/**
 * Call the Varnish server to purge the cache with GoDaddy.
 *
 * @since 3.9.1 deprecated
 * @since 2.9.5
 *
 * @return void
 */
function rocket_clean_domain_godaddy() {
	_deprecated_function( __FUNCTION__ . '()', '3.9.1', '\WP_Rocket\ThirdParty\Hostings\Godaddy::clean_domain_godaddy' );
	rocket_godaddy_request( 'BAN' );
}

/**
 * Call the Varnish server to purge a specific URL with GoDaddy.
 *
 * @since 3.9.1 deprecated
 * @since 2.9.5
 *
 * @param string $url URL to purge.
 * @return void
 */
function rocket_clean_file_godaddy( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.9.1', '\WP_Rocket\ThirdParty\Hostings\Godaddy::clean_file_godaddy' );
	rocket_godaddy_request( 'PURGE', home_url( $url ) );
}

/**
 * Call the Varnish server to purge the home with GoDaddy.
 *
 * @since 3.9.1 deprecated
 * @since 2.9.5
 *
 * @param string $root root URL.
 * @param string $lang language code.
 * @return void
 */
function rocket_clean_home_godaddy( $root, $lang ) {
	_deprecated_function( __FUNCTION__ . '()', '3.9.1', '\WP_Rocket\ThirdParty\Hostings\Godaddy::clean_home_godaddy' );
	$home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
	$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base );

	rocket_godaddy_request( 'PURGE', $home_url );
	rocket_godaddy_request( 'PURGE', $home_pagination_url );
}

/**
 * Perform the call to the Varnish server to purge
 *
 * @since 3.9.1 deprecated
 * @since 2.9.5
 * @source WPaaS\Cache
 *
 * @param string $method can be BAN or PURGE.
 * @param string $url URL to purge.
 * @return void
 */
function rocket_godaddy_request( $method, $url = null ) {
	_deprecated_function( __FUNCTION__ . '()', '3.9.0.4', '\WP_Rocket\ThirdParty\Hostings\Godaddy::godaddy_request' );
	if ( ! method_exists( 'WPaas\Plugin', 'vip' ) ) {
		return;
	}

	if ( empty( $url ) ) {
		$url = home_url();
	}

	$host = rocket_extract_url_component( $url, PHP_URL_HOST );
	$url  = set_url_scheme( str_replace( $host, WPaas\Plugin::vip(), $url ), 'http' );

	wp_cache_flush();

	// This forces the APC cache to flush across the server.
	update_option( 'gd_system_last_cache_flush', time() );

	wp_remote_request(
		esc_url_raw( $url ),
		[
			'method'   => $method,
			'blocking' => false,
			'headers'  => [
				'Host' => $host,
			],
		]
	);
}
