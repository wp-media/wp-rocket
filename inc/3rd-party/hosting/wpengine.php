<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'WpeCommon' ) && function_exists( 'wpe_param' ) ) :

/**
 * Conflict with WP Engine caching system
 *
 * @since 2.6.4
 */
add_action( 'init', '__rocket_stop_generate_caching_files_on_wpengine' );
function __rocket_stop_generate_caching_files_on_wpengine() {
	add_filter( 'do_rocket_generate_caching_files', '__return_false' );
}

/**
 * Run WP Rocket preload bot after purged the Varnish cache via WP Engine Hosting
 *
 * @since 2.6.4
 *
 * @return void
 */
add_action( 'admin_init', '__rocket_run_rocket_bot_after_wpengine' );
function __rocket_run_rocket_bot_after_wpengine() {
	if ( wpe_param( 'purge-all' ) && defined( 'PWP_NAME' ) && check_admin_referer( PWP_NAME . '-config' ) ) {
		// Preload cache
		run_rocket_preload_cache( 'cache-preload' );
	}
}

/**
 * Add WP Engine CDN CNAMES to the list of allowed CNAMES
 * Note: we need to auto-activate the CDN our CDN option
 *
 * @since 2.7
 *
 * @return void
 */
add_filter( 'get_rocket_option_cdn', '__rocket_auto_activate_cdn_on_wpengine' );
function __rocket_auto_activate_cdn_on_wpengine( $value ) {
	$cdn_domain = rocket_get_wp_engine_cdn_domain();
	
	if ( ! empty( $cdn_domain ) ) {
		$value = true;
	}
	
	return $value;
}

add_filter( 'rocket_cdn_cnames', '__rocket_add_wpengine_cdn_cnames' );
function __rocket_add_wpengine_cdn_cnames( $hosts ) {
	$cdn_domain = rocket_get_wp_engine_cdn_domain();

	if ( ! empty( $cdn_domain ) ) {
		$hosts[] = $cdn_domain;
	}

	return $hosts;
}

/* @since 2.6.4
 * For not conflit with WP Engine
*/
add_action( 'after_rocket_clean_domain', 'rocket_clean_wpengine' );

/**
 * Call the cache server to purge the cache with WP Engine hosting.
 *
 * @since 2.6.4
 *
 * @return void
 */
function rocket_clean_wpengine() {	
	if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
		WpeCommon::purge_memcached();
	}
	
	if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
		WpeCommon::purge_varnish_cache();
	}
}

/**
  * Don't display the Varnish options tab for WP Engine users
  *
  * @since 2.7
 */
add_filter( 'rocket_display_varnish_options_tab', '__return_false' );

/**
  * Gets WP Engine CDN Domain
  *
  * @since 2.8.6
  * @author Jonathan Buttigieg
  *
  * return string $cdn_domain the WP Engine CDN Domain
 */
function rocket_get_wp_engine_cdn_domain() {
    global $wpe_netdna_domains, $wpe_netdna_domains_secure;
   
    $cdn_domain = '';
    $is_ssl     = @$_SERVER['HTTPS'];
   
    if ( preg_match( '/^[oO][fF]{2}$/', $is_ssl ) ) {
        $is_ssl = false;  // have seen this!
    }
   
    $native_schema = $is_ssl ? "https" : "http";
   
    // Determine the CDN, if any
    if ( $is_ssl ) {
        $domains = $wpe_netdna_domains_secure;
    } else {
        $domains = $wpe_netdna_domains;
    }
   
    $wpengine   = WpeCommon::instance();
    $cdn_domain = $wpengine->get_cdn_domain( $domains, home_url(), $is_ssl );
    
    if ( ! empty( $cdn_domain ) ) {
		$cdn_domain = $native_schema . '://' . $cdn_domain;
    }
   
    return $cdn_domain;
}

/**
 * Always keep WP_CACHE constant to true
 *
 * @since 2.8.6
 */
add_filter( 'set_rocket_wp_cache_define', '__return_true' );

endif;