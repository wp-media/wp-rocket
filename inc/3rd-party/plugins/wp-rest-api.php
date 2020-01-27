<?php

defined( 'ABSPATH' ) || exit;

/**
 * Add WP REST API path to cache exclusion
 *
 * @since 2.5.12
 *
 * @param array $uri URLs to exclude from cache.
 * @return array Updated URLs
 */
function rocket_exclude_wp_rest_api( $uri ) {
	global $wp_rewrite;

	/**
	 * By default, don't cache the WP REST API.
	 *
	 * @since 2.5.12
	 *
	 * @param bool false will force to cache the WP REST API
	 */
	$reject_wp_rest_api = apply_filters( 'rocket_cache_reject_wp_rest_api', true );

	if ( ! $reject_wp_rest_api ) {
		return $uri;
	}

	/**
	 * `(/[^/]+)?` is used instead of `(/.+)?` to match only one level.
	 * This prevents to match a taxonomy term named `wp-json` (on multisite, the main site's posts and taxonomy archives are prefixed with `blog` => example.com/blog/category/wp-json/).
	 */
	$prefix = rocket_is_subfolder_install() ? '(/[^/]+)?' : '';
	$index  = ! empty( $wp_rewrite->index ) ? $wp_rewrite->index : 'index.php';
	$index  = preg_quote( $index, '/' );
	$suffix = rest_get_url_prefix();
	$suffix = preg_quote( trim( $suffix, '/' ), '/' );

	/**
	 * Results in:
	 * - Single site:        (/index\.php)?/wp\-json(/.*|$)
	 * - Multisite: (/[^/]+)?(/index\.php)?/wp\-json(/.*|$)
	 */
	$uri[] = $prefix . '/(' . $index . '/)?' . $suffix . '(/.*|$)';

	return $uri;
}
add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_wp_rest_api' );
