<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


/**
 * Launch DNS Prefetching process
 *
 * @since 2.1 Adding CNAMES fo CDN automatically in DNS Prefetch process
 * @since 2.0
 *
 */

add_filter( 'rocket_buffer', 'rocket_dns_prefetch', 12 );
function rocket_dns_prefetch( $buffer )
{
	$dns_link_tags = '';

	// Get all domains to prefetch DNS
	// It's possible to add domains for specific conditions with 'rocket_dns_prefetch' filter
	$domains = array_merge( get_rocket_cdn_cnames( array( 'all' ) ), get_rocket_option( 'dns_prefetch', array() ) );
	$domains = apply_filters( 'rocket_dns_prefetch', $domains );

	if ( count( $domains ) ) {
		
		foreach ( $domains as $domain ) {
			$dns_link_tags .= '<link rel="dns-prefetch" href="' . esc_url( $domain ) . '" />';
		}
			
	}

	// Insert all DNS prefecth tags in head
	$buffer = preg_replace( '/<head(.*)>/', '<head$1><!--[if IE]><![endif]-->' . $dns_link_tags, $buffer, 1 );

	return $buffer;
}