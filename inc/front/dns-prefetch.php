<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Launch DNS Prefetching process
 *
 * @since 2.1 Adding CNAMES fo CDN automatically in DNS Prefetch process
 * @since 2.0
 */
add_filter( 'rocket_buffer', 'rocket_dns_prefetch', 12 );
function rocket_dns_prefetch( $buffer )
{
	$dns_link_tags = '';
	$domains = array_merge( get_rocket_cdn_cnames(), get_rocket_option( 'dns_prefetch', array() ) );

	/**
	 * Filter list of domains to prefetch DNS
	 *
	 * @since 1.1.0
	 *
	 * @param array $domains List of domains to prefetch DNS
	 */
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