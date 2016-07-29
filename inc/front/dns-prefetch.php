<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Launch DNS Prefetching process
 *
 * @since 2.8.9 Use the wp_resource_hints filter introduced in WP 4.6 if possible, fallback to rocket_buffer hook for previous versions
 * @since 2.5 Don't add CNAMES if CDN is disabled HTTPS pages or on specific posts
 * @since 2.1 Adding CNAMES fo CDN automatically in DNS Prefetch process
 * @since 2.0
 */
if ( function_exists( 'wp_resource_hints' ) ) {
    add_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
} else {
    add_filter( 'rocket_buffer', '__rocket_dns_prefetch_buffer', 12 );
}

/**
 * Adds domain names to the list of DNS Prefetch printed by wp_resource_hints
 *
 * @since 2.8.9
 * @author Remy Perona
 *
 * @param Array $hints URLs to print for resource hints
 * @param String $relation_type The relation type the URL are printed for
 * @return Array URLs to print
 */ 
function rocket_dns_prefetch( $hints, $relation_type ) {
    $domains = rocket_get_dns_prefetch_domains();
    
    if ( (bool) $domains ) {
        foreach ( $domains as $domain ) {
            if ( 'dns-prefetch' === $relation_type ) {
                $hints[] = $domain;
            }
        }
    }

    return $hints;
}

/**
 * Inserts html code for domain names to DNS prefetch in the buffer before creating the cache file
 *
 * @since 2.0
 * @author Jonathan Buttigieg
 *
 * @param String $buffer
 * @return String Updated buffer
 */ 
function __rocket_dns_prefetch_buffer( $buffer ) {
	$dns_link_tags = '';
	$domains       = rocket_get_dns_prefetch_domains();

	if ( (bool) $domains ) {
		foreach ( $domains as $domain ) {
			$dns_link_tags .= '<link rel="dns-prefetch" href="' . esc_url( $domain ) . '" />';
		}
	}

	$old_ie_conditional_tag = '';
	
	/**
	 * Allow to print an empty IE conditional tag to speed up old IE versions to load CSS & JS files
	 *
	 * @since 2.6.5
	 *
	 * @param bool true will print the IE conditional tag
	 */
	if ( apply_filters( 'do_rocket_old_ie_prefetch_conditional_tag', true ) ) {
		$old_ie_conditional_tag = '<!--[if IE]><![endif]-->';
	}

	// Insert all DNS prefecth tags in head
	$buffer = preg_replace( '/<head(.*)>/', '<head$1>' . $old_ie_conditional_tag . $dns_link_tags, $buffer, 1 );

	return $buffer;
}

/**
 * Get the domain names to DNS prefetch from WP Rocket options
 * 
 * @since 2.8.9
 * @author Remy Perona
 *
 * return Array An array of domain names to DNS prefetch
 */
function rocket_get_dns_prefetch_domains() {
	$cdn_cnames    = get_rocket_cdn_cnames( array( 'all', 'images', 'css_and_js', 'css', 'js' ) );

	// Don't add CNAMES if CDN is disabled HTTPS pages or on specific posts
	if ( ! is_rocket_cdn_on_ssl() || is_rocket_post_excluded_option( 'cdn' ) ) {
		$cdn_cnames = array();
	}

	$domains = array_merge( $cdn_cnames, (array) get_rocket_option( 'dns_prefetch' ) );

	/**
	 * Filter list of domains to prefetch DNS
	 *
	 * @since 1.1.0
	 *
	 * @param array $domains List of domains to prefetch DNS
	 */
	return apply_filters( 'rocket_dns_prefetch', $domains );
}