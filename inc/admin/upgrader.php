<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/*
 * Tell WP what to do when admin is loaded aka upgrader
 *
 * since 1.0
 *
 */

add_action( 'admin_init', 'rocket_upgrader' );
function rocket_upgrader()
{
	// Grab some infos
	$options = get_option( WP_ROCKET_SLUG );
	$actual_version = isset( $options['version'] ) ? $options['version'] : false;
	// You can hook the upgrader to trigger any action when WP Rocket is upgraded
	if( !$actual_version ){ // first install
		do_action( 'wp_rocket_first_install' );
	}elseif( WP_ROCKET_VERSION != $actual_version ){ // already installed but got updated
		do_action( 'wp_rocket_upgrade', WP_ROCKET_VERSION, $actual_version );
	}

	// If any upgrade has been done, we flush and update version #
	if( did_action( 'wp_rocket_first_install' ) || did_action( 'wp_rocket_upgrade' ) ){
		flush_rocket_htaccess();
		flush_rewrite_rules();
		$options = get_option( WP_ROCKET_SLUG );
		$options['version'] = WP_ROCKET_VERSION;
		if( isset( $options['consumer_key'] ) && $options['consumer_key']==hash( 'crc32', rocket_get_domain( home_url() ) ) ){
			$response = wp_remote_get( WP_ROCKET_WEB_VALID, array( 'timeout'=>30 ) );
			if( !is_a($response, 'WP_Error') && strlen( $response['body'] )==32 )
				$options['secret_key'] = $response['body'];
		}else{
				unset( $options['secret_key'] );
		}
		update_option( WP_ROCKET_SLUG, $options );
	}

	if( !rocket_valid_key() && current_user_can( 'manage_options' ) )
		add_action( 'admin_notices', 'rocket_need_api_key' );

}

/* BEGIN UPGRADER'S HOOKS */

/**
 * Keeps this function up to date at each version
 *
 * since 1.0
 *
 */

add_action( 'wp_rocket_first_install', 'rocket_first_install' );
function rocket_first_install()
{

	// Create Option
	add_option( WP_ROCKET_SLUG,
		array(
			'cache_mobile'         	=> 0,
			'cache_reject_uri'     	=> array(),
			'cache_reject_cookies' 	=> array(),
			'cache_purge_pages'  	=> array(),
			'purge_cron_interval'  	=> 4,
			'purge_cron_unit'  		=> 'HOUR_IN_SECONDS',
			'exclude_css'		   	=> array(),
			'exclude_js'		   	=> array(),
			'deferred_js_files'	   	=> array(),
			'deferred_js_wait'	   	=> array(),
			'lazyload'			   	=> 0,
			'minify_css'		   	=> 0,
			'minify_js'			   	=> 0,
			// 'cut_concat'		   	=> 0,
		)
	);
}

/**
 * What to do when Rocket is updated, depending on versions
 *
 * since 1.0
 *
 */

add_action( 'wp_rocket_upgrade', 'rocket_new_upgrade', 10, 2 );
function rocket_new_upgrade( $wp_rocket_version, $actual_version )
{
	if( version_compare( $actual_version, '1.0.1', '<' ) ){
		wp_clear_scheduled_hook( 'rocket_check_event' );
	}
}

/* END UPGRADER'S HOOKS */