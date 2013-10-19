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
	$actual_version = get_rocket_option( 'version' );
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
		rocket_renew_all_boxes( 0, 'rocket_warning_plugin_modification' );
		$options = get_option( WP_ROCKET_SLUG ); // do not use get_rocket_option() here
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

	if( !rocket_valid_key() && current_user_can( 'manage_options' ) ){
		add_action( 'admin_notices', 'rocket_need_api_key' );
		add_filter( 'rocket_pointer_apikey', '__return_true' );
	}

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
			'minify_html'			=> 0,
			'dns_prefetch'			=> 0
		)
	);
	rocket_dismiss_box( 'rocket_warning_plugin_modification' );
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
	if( version_compare( $actual_version, '1.0.1', '<' ) )
	{
		wp_clear_scheduled_hook( 'rocket_check_event' );
	}
	
	if( version_compare( $actual_version, '1.2.0', '<' ) )
	{
		// Delete old WP Rocket cache dir
		rocket_rrmdir( WP_ROCKET_PATH . 'cache' );
		
		// Create new WP Rocket cache dir
		if( !is_dir( WP_ROCKET_CACHE_PATH ) )
			mkdir( WP_ROCKET_CACHE_PATH );
	}
	
	if( version_compare( $actual_version, '1.3.0', '<' ) )
	{
		rocket_dismiss_boxes( array( 'box'=>'rocket_warning_plugin_modification', '_wpnonce'=>wp_create_nonce( 'rocket_ignore_rocket_warning_plugin_modification' ), 'action'=>'rocket_ignore' ) );
	}
	
	if( version_compare( $actual_version, '1.3.3', '<' ) )
	{
		
		// Clean cache
		rocket_clean_domain();
		// Create cache files
		run_rocket_bot( 'cache-preload' );
	}
	
	if( version_compare( $actual_version, '1.4.0', '<' ) )
	{
		
		global $wp_filesystem;
	    if( !$wp_filesystem )
	    {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
			require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
			$wp_filesystem = new WP_Filesystem_Direct( new StdClass() );
		}
		
		// Get chmod of old folder cache
		$chmod = substr( sprintf( '%o', fileperms( WP_CONTENT_DIR . '/wp-rocket-cache') ), -4 );
		
		// Check and create cache folder in wp-content if not already exist
		if( !$wp_filesystem->is_dir( WP_CONTENT_DIR . '/cache' ) )
			$wp_filesystem->mkdir( WP_CONTENT_DIR . '/cache', octdec($chmod) );
		
		// Move old cache folder in new path
		$wp_filesystem->move( WP_CONTENT_DIR . '/wp-rocket-cache', WP_ROCKET_CACHE_PATH );
	}
}

/* END UPGRADER'S HOOKS */