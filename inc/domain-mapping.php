<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Used to get compatibility between multidomain and get_rocket_parse_url()
 *
 * @since 2.2
 */
add_filter( 'rocket_parse_url', 'rocket_parse_url_domain_mapping' );
function rocket_parse_url_domain_mapping( $url ) {
	$original_siteurl_host       = parse_url( get_original_url( 'siteurl' ), PHP_URL_HOST );
	$domain_mapping_siteurl_host = parse_url( domain_mapping_siteurl( false ), PHP_URL_HOST );

	if ( false === strpos( $domain_mapping_siteurl_host, $original_siteurl_host ) ) {
		$url[0] = str_replace( $original_siteurl_host, $domain_mapping_siteurl_host, $url[0] );
	}
	
	return $url;
}

/**
 * Used to get compatibility between multidomain and rocket_clean_files() & rocket_clean_domain()
 *
 * @since 2.6.5 Add compatibility with rocket_clean_domain()
 * @since 2.2
 */
if ( function_exists( 'domain_mapping_post_content' ) ) :
	add_filter( 'rocket_clean_files'		, 'domain_mapping_post_content' );
	add_filter( 'rocket_clean_domain_urls'	, 'domain_mapping_post_content' );
	add_filter( 'rocket_post_purge_urls'	, 'domain_mapping_post_content' );
endif;

/**
 * Used to get compatibility between multidomain and rocket_clean_home() 
 *
 * @since 2.6.5
 */
add_filter( 'rocket_clean_home_root', '__rocket_clean_home_root_for_domain_mapping_siteurl', 10, 3 );
function __rocket_clean_home_root_for_domain_mapping_siteurl( $root, $host, $path ) {	
	$original_siteurl_host       = parse_url( get_original_url( 'siteurl' ), PHP_URL_HOST );
	$domain_mapping_siteurl_host = parse_url( domain_mapping_siteurl( false ), PHP_URL_HOST );
	
	if ( $original_siteurl_host != $domain_mapping_siteurl_host ) {
		$root = WP_ROCKET_CACHE_PATH . $host . '*';
	}
	
	return $root;
} 