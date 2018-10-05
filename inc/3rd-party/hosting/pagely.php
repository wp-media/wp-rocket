<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * Clear WP Rocket cache after purged the Varnish cache via Pagely hosting
 *
 * @since 2.5.7
 *
 * @return void
 */
function rocket_clear_cache_after_pagely() {
	// Clear all caching files.
	rocket_clean_domain();
}
add_action( 'pagely_cache_purge_after', 'rocket_clear_cache_after_pagely' );

/**
 * Call the cache server to purge the cache with Pagely hosting.
 *
 * @since 2.5.7
 *
 * @return void
 */
function rocket_clean_pagely() {
	if ( class_exists( 'PagelyCachePurge' ) ) {
			$purger = new PagelyCachePurge();
			$purger->purgeAll();
	}
}
add_action( 'after_rocket_clean_domain', 'rocket_clean_pagely' );
