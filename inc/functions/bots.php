<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Launch the Robot
 *
 * @since 1.0
 *
 */

function run_rocket_bot( $spider = 'cache-preload', $start_url = '' )
{

	if( $spider == 'cache-preload' && empty( $start_url ) )
		$start_url = home_url();
	else if( $spider == 'cache-json' )
		$start_url = WP_ROCKET_URL . 'cache.json';

	if( empty( $start_url ) )
		return false;

	do_action( 'before_run_rocket_bot' );

	wp_remote_get( WP_ROCKET_BOT_URL.'?spider=' . $spider . '&start_url=' . $start_url );

	do_action( 'after_run_rocket_bot' );
}



/**
 * Launch the Cache Preload Robot for all active languages
 *
 * @since 2.0
 *
 */

function run_rocket_bot_for_selected_lang( $lang )
{

	// Check if WPML is activated
	if( rocket_is_plugin_active('sitepress-multilingual-cms/sitepress.php') )
	{

		global $sitepress;
		$url = $sitepress->language_url( $lang );
	}
	else if( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) )
	{
		$url = qtrans_convertURL( home_url(), $lang, true );
	}

	run_rocket_bot( 'cache-preload', $url );
}



/**
 * Launch the Cache Preload Robot for all active languages
 *
 * @since 2.0
 *
 */

function run_rocket_bot_for_all_langs()
{

	$langs = get_rocket_all_active_langs_uri();
	foreach ( $langs as $lang )
		run_rocket_bot( 'cache-preload', $lang );
}