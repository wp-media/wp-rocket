<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Adds domain names to the list of DNS Prefetch printed by wp_resource_hints
 *
 * @since 2.8.9
 * @author Remy Perona
 *
 * @param Array  $hints URLs to print for resource hints.
 * @param String $relation_type The relation type the URL are printed for.
 * @return Array URLs to print
 */
function rocket_dns_prefetch( $hints, $relation_type ) {

	// Don't add prefetch for uncached pages.
	if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $hints;
	}

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
add_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );

