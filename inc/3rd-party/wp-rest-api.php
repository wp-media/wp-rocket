<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Add WP REST API path to cache exclusion
 *
 * @since 2.5.12
 *
 * @param array $uri URLs to exclude from cache.
 * @return array Updated URLs
 */
function rocket_exclude_wp_rest_api( $uri ) {
	/**
	 * By default, don't cache the WP REST API.
	 *
	 * @since 2.5.12
	 *
	 * @param bool false will force to cache the WP REST API
	 */
	$rocket_cache_reject_wp_rest_api = apply_filters( 'rocket_cache_reject_wp_rest_api', true );

	// Exclude WP REST API.
	if ( function_exists( 'json_get_url_prefix' ) && $rocket_cache_reject_wp_rest_api ) {
		$uri[] = '/' . json_get_url_prefix() . '/(.*)';
	}

	if ( class_exists( 'WP_REST_Controller' ) && $rocket_cache_reject_wp_rest_api ) {
		$uri[] = '/wp-json/(.*)';
	}

	return $uri;
}
add_filter( 'rocket_cache_reject_uri', 'rocket_exclude_wp_rest_api' );
