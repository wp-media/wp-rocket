<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * When Woocommerce, EDD, iThemes Exchange, Jigoshop & WP-Shop options are saved or deleted,
 * we update .htaccess & config file to get the right checkout page to exclude to the cache.
 *
 * @since 2.6 Add support with SF Move Login & WPS Hide Login to exclude login pages
 * @since 2.4
 */
add_action( 'update_option_woocommerce_cart_page_id'	, '__rocket_after_update_single_options', 10, 2 );
add_action( 'update_option_woocommerce_checkout_page_id', '__rocket_after_update_single_options', 10, 2 );
add_action( 'update_option_woocommerce_myaccount_page_id', '__rocket_after_update_single_options', 10, 2 );
add_action( 'update_option_wpshop_cart_page_id'			, '__rocket_after_update_single_options', 10, 2 );
add_action( 'update_option_wpshop_checkout_page_id'		, '__rocket_after_update_single_options', 10, 2 );
add_action( 'update_option_wpshop_payment_return_page_id', '__rocket_after_update_single_options', 10, 2 );
add_action( 'update_option_wpshop_payment_return_nok_page_id', '__rocket_after_update_single_options', 10, 2 );
add_action( 'update_option_wpshop_myaccount_page_id'	, '__rocket_after_update_single_options', 10, 2 );
add_action( 'update_option_it-storage-exchange_settings_pages', '__rocket_after_update_single_options', 10, 2 );
add_action( 'update_option_sfml', '__rocket_after_update_single_options', 10, 2 );
add_action( 'update_option_whl_page', '__rocket_after_update_single_options', 10, 2 );
function __rocket_after_update_single_options( $old_value, $value ) {
	if ( $old_value != $value ) {
		// Update .htaccess file rules
		flush_rocket_htaccess();
	
		// Update config file
		rocket_generate_config_file();	
	}
}

/**
 * We need to regenerate the config file + htaccess depending on some plugins
 *
 * @since 2.6.5 Add support with SF Move Login & WPS Hide Login
 */
add_action( 'activate_sf-move-login/sf-move-login.php', 'rocket_generate_config_file', 11 );
add_action( 'deactivate_sf-move-login/sf-move-login.php', 'rocket_generate_config_file', 11 );
add_action( 'activate_wps-hide-login/wps-hide-login.php', 'rocket_generate_config_file', 11 );
add_action( 'deactivate_wps-hide-login/wps-hide-login.php', 'rocket_generate_config_file', 11 );

add_action( 'activate_sf-move-login/sf-move-login.php', 'flush_rocket_htaccess', 11 );
add_action( 'deactivate_sf-move-login/sf-move-login.php', 'flush_rocket_htaccess', 11 );
add_action( 'activate_wps-hide-login/wps-hide-login.php', 'flush_rocket_htaccess', 11 );
add_action( 'deactivate_wps-hide-login/wps-hide-login.php', 'flush_rocket_htaccess', 11 );

add_action( 'update_option_edd_settings', '__rocket_after_update_array_options', 10, 2 );
add_action( 'update_option_jigoshop_options', '__rocket_after_update_array_options', 10, 2 );
function __rocket_after_update_array_options( $old_value, $value ) {
	$options = array( 
		'purchase_page', 
		'jigoshop_cart_page_id', 
		'jigoshop_checkout_page_id', 
		'jigoshop_myaccount_page_id' 
	);
	
	foreach ( $options as $val ) {
		if ( ( ! isset( $old_value[ $val ] ) && isset( $value[ $val ] ) ) ||
			( isset( $old_value[ $val ], $value[ $val ] ) && $old_value[ $val ] != $value[ $val ] ) 
		) {
			// Update .htaccess file rules
			flush_rocket_htaccess();
		
			// Update config file
			rocket_generate_config_file();	
			break;
		}
	}
}

/**
 * Compatibility with an usual NGINX configuration which include 
 * try_files $uri $uri/ /index.php?q=$uri&$args
 *
 * @since 2.3.9
 */
add_filter( 'rocket_cache_query_strings', '__rocket_better_nginx_compatibility' );
function __rocket_better_nginx_compatibility( $query_strings ) {
	global $is_nginx;
	
	if ( $is_nginx ) {
		$query_strings[] = 'q';
	}
	
	return $query_strings;
}

/**
 * Clear WP Rocket cache after purged the StudioPress Accelerator cache 
 *
 * @since 2.5.5
 *
 * @return void
 */
add_action( 'admin_init', '__rocket_clear_cache_after_studiopress_accelerator' );
function __rocket_clear_cache_after_studiopress_accelerator() {
	if ( isset( $GLOBALS['sp_accel_nginx_proxy_cache_purge'] ) && is_a( $GLOBALS['sp_accel_nginx_proxy_cache_purge'], 'SP_Accel_Nginx_Proxy_Cache_Purge' ) && isset( $_REQUEST['_wpnonce'] ) ) {	
		$nonce = $_REQUEST['_wpnonce'];
		if (wp_verify_nonce($nonce, 'sp-accel-purge-url') && !empty($_REQUEST['cache-purge-url'])) {
			$submitted_url = $_REQUEST['cache-purge-url'];
			
			// Clear the URL
			rocket_clean_files( array( $submitted_url ) );
		} else if (wp_verify_nonce($nonce, 'sp-accel-purge-theme')) {
			// Clear all caching files
			rocket_clean_domain();
			
			// Preload cache
			run_rocket_preload_cache( 'cache-preload' );
		}	
	}
}

/**
 * Clear WP Rocket cache after purged the Varnish cache via Varnish HTTP Purge plugin
 *
 * @since 2.5.5
 *
 * @return void
 */
add_action( 'admin_init', '__rocket_clear_cache_after_varnish_http_purge' );
function __rocket_clear_cache_after_varnish_http_purge() {
	if ( class_exists( 'VarnishPurger' ) && isset( $_GET['vhp_flush_all'] ) && current_user_can( 'manage_options' ) && check_admin_referer( 'varnish-http-purge' ) ) {
		// Clear all caching files
		rocket_clean_domain();
		
		// Preload cache
		run_rocket_preload_cache( 'cache-preload' );
	}
}

/**
 * Clear WP Rocket cache after purged the Varnish cache via Pagely hosting
 *
 * @since 2.5.7
 *
 * @return void
 */
add_action( 'pagely_page_purge-cache', '__rocket_clear_cache_after_pagely' );
function __rocket_clear_cache_after_pagely() {
	// Clear all caching files
	rocket_clean_domain();
		
	// Preload cache
	run_rocket_preload_cache( 'cache-preload' );
}

/**
 * Clear WP Rocket cache after purged the Varnish cache via Pressidium Hosting
 *
 * @since 2.5.11
 *
 * @return void
 */
add_action( 'admin_init', '__rocket_clear_cache_after_pressidium' );
function __rocket_clear_cache_after_pressidium() {
	if ( isset( $_POST['purge-all'] ) && current_user_can( 'manage_options' ) && defined( 'WP_NINUKIS_WP_NAME' ) && check_admin_referer( WP_NINUKIS_WP_NAME . '-caching' ) ) {
		// Clear all caching files
		rocket_clean_domain();
		
		// Preload cache
		run_rocket_preload_cache( 'cache-preload' );
	}
}