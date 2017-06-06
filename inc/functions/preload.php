<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Launches the automatic partial preload
 *
 * @since X.X
 * @author Remy Perona
 *
 * @param array $preload_urls An array of URLs to preload.
 * @return void
 */
function run_rocket_automatic_preload( $preload_urls ) {
	if ( ! get_rocket_option( 'partial_preload', 0 ) ) {
		return;
	}

	global $rocket_partial_preload_process;
	$rocket_partial_preload_process->cancel_process();
	
	foreach ( $preload_urls as $preload_url ) {
		$rocket_partial_preload_process->push_to_queue( $preload_url );
	}

	$rocket_partial_preload_process->save()->dispatch();
}

/**
 * Launches the sitemap preload
 *
 * @since 2.8
 * @author Remy Perona
 *
 * @return bool\void False if no sitemaps, void otherwise
 */
function run_rocket_sitemap_preload() {
	/*
     * Filters the sitemaps list to preload
     *
     * @since 2.8
     *
     * @param array Array of sitemaps URL
     */
	$sitemaps = apply_filters( 'rocket_sitemap_preload_list', get_rocket_option( 'sitemaps', false ) );

	if ( ! $sitemaps ) {
		return false;
	}

	$sitemaps   = array_flip( array_flip( $sitemaps ) );
	$urls_group = array();

	global $rocket_sitemap_preload_process;
	$rocket_sitemap_preload_process->cancel_process();

	foreach ( $sitemaps as $sitemap_type => $sitemap_url ) {
		/**
		 * Fires before WP Rocket sitemap preload is called for a sitemap URL
		 *
		 * @since 2.8
		 *
		 * @param string $sitemap_type 	the sitemap identifier
		 * @param string $sitemap_url sitemap URL to be crawler
		*/
		do_action( 'before_run_rocket_sitemap_preload', $sitemap_type, $sitemap_url );

		$urls_group[] = rocket_process_sitemap( $sitemap_url );

		/**
		 * Fires after WP Rocket sitemap preload was called for a sitemap URL
		 *
		 * @since 2.8
		 *
		 * @param string $sitemap_type 	the sitemap identifier
		 * @param string $sitemap_url sitemap URL crawled
		*/
		do_action( 'after_run_rocket_sitemap_preload', $sitemap_type, $sitemap_url );
	}

	foreach ( $urls_group as $k => $urls ) {
		if ( ! (bool) $urls ) {
			continue;
		}

		$urls = array_flip( array_flip( $urls ) );
		foreach ( $urls as $url ) {
			$rocket_sitemap_preload_process->push_to_queue( $url );
		}
	}

	$rocket_sitemap_preload_process->save()->dispatch();
}

/**
 * Processes the sitemaps recursively
 *
 * @since 2.8
 * @author Remy Perona
 *
 * @param string $sitemap_url URL of the sitemap.
 * @param array  $urls An array of URLs.
 * @return array Empty array or array containing URLs
 */
function rocket_process_sitemap( $sitemap_url, $urls = array() ) {
	$tmp_urls = array();

	$args = array(
		'timeout'    => 0.01,
		'blocking'   => false,
		'user-agent' => 'wprocketbot',
		'sslverify'  => apply_filters( 'https_local_ssl_verify', true ),
	);

	$sitemap = wp_remote_get( esc_url_raw( $sitemap_url ) );

	if ( 200 !== wp_remote_retrieve_response_code( $sitemap ) ) {
		return array();
	}

	$xml_data = wp_remote_retrieve_body( $sitemap );

	if ( ! (bool) $xml_data ) {
		return array();
	}

	libxml_use_internal_errors( true );

	$xml = simplexml_load_string( $xml_data );

	if ( false === $xml ) {
		libxml_clear_errors();
		return array();
	}

	$url_count = count( $xml->url );

	if ( $url_count > 0 ) {
		for ( $i = 0; $i < $url_count; $i++ ) {
			$page_url = (string) $xml->url[ $i ]->loc;
			$tmp_urls[] = $page_url;
		}
	} else {
		// Sub sitemap?
		$sitemap_children = count( $xml->sitemap );
		if ( $sitemap_children > 0 ) {
			for ( $i = 0; $i < $sitemap_children; $i++ ) {
				$sub_sitemap_url = (string) $xml->sitemap[ $i ]->loc;
				$urls = rocket_process_sitemap( $sub_sitemap_url, $urls );
			}
		}
	}

	$urls = array_merge( $urls, $tmp_urls );
	return $urls;
}
