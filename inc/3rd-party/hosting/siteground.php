<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Call the cache server to purge the cache with SuperCacher (SiteGround) Pretty good hosting!
 *
 * @since 2.3
 *
 * @return void
 */
function rocket_clean_supercacher() {
	if ( isset( $GLOBALS['sg_cachepress_supercacher'] ) && is_a( $GLOBALS['sg_cachepress_supercacher'], 'SG_CachePress_Supercacher' ) ) {
		$GLOBALS['sg_cachepress_supercacher']->purge_cache();
	}
}
add_action( 'wp_ajax_sg-cachepress-purge'   , 'rocket_clean_domain', 0 );
add_action( 'admin_post_sg-cachepress-purge', 'rocket_clean_domain', 0 );
add_action( 'after_rocket_clean_domain'     , 'rocket_clean_supercacher' );
