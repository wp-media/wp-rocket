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
		update_option( WP_ROCKET_SLUG, $options );
	}

	if( !rocket_valid_key() && current_user_can( 'manage_options' ) )
		add_action( 'admin_notices', 'rocket_need_api_key' );

}

/* BEGIN UPGRADER'S HOOKS */

// Keeps this function up to date at each version
add_action( 'wp_rocket_first_install', 'rocket_first_install' );
function rocket_first_install()
{
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
			'lazyload'			   	=> 1,
			'minify_css'		   	=> 0,
			'minify_js'			   	=> 0,
		)
	);
}
/* END UPGRADER'S HOOKS */

add_action( 'wp_rocket_upgrade', 'new_upgrade', 10, 2 );
function new_upgrade( $wp_rocket_version, $actual_version )
{
	if( $actual_version<3){
	}else{
	}
}