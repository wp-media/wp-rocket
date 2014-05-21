<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Used to get compatibility with multidomain with get_rocket_parse_url()
 *
 * @since 2.2
 *
 */

add_filter( 'rocket_parse_url', '__rocket_replace_domain_mapping_siteurl' );
function __rocket_replace_domain_mapping_siteurl( $url ) {
	
	$original_siteurl_host       = parse_url( get_original_url( 'siteurl' ), PHP_URL_HOST );
	$domain_mapping_siteurl_host = parse_url( domain_mapping_siteurl( false ), PHP_URL_HOST );
	
	$url[0] = str_replace( $original_siteurl_host, $domain_mapping_siteurl_host, $url[0] );
	return $url;

}



/**
 * Used to get compatibility with multidomain with rocket_clean_files()
 *
 * @since 2.2
 *
 */

add_filter( 'rocket_clean_files', '__rocket_clean_files_domain_mapping' );
function __rocket_clean_files_domain_mapping( $urls ) {

	$urls = array_map( '__rocket_replace_domain_mapping_siteurl' , $urls );
	return $urls;

}