<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


/**
 * Launch the Robot
 *
 * @since 1.0
 *
 */

function run_rocket_bot( $spider = 'cache-preload', $start_url = '' )
{

	/**
	 * Filter to manage the bot job
	 *
	 * @since 2.1
	 * @param bool Do the job or not 
	 * @param string $spider The spider name
	*/
	if ( ! apply_filters( 'do_run_rocket_bot', true, $spider ) ) {
		return false;
	}
	
	if ( $spider == 'cache-preload' && empty( $start_url ) ) {		
		$start_url = home_url();		
	} elseif ( $spider == 'cache-json' ) {	
		$start_url = WP_ROCKET_URL . 'cache.json';
	}

	if ( empty( $start_url ) ) {
		return false;
	}

	/**
	 * Fires before WP Rocket Bot is called
	 *
	 * @since 1.1.0
	 * @param string $spider The spider name
	 * @param string $start_url URL that crawl by the bot
	*/
	do_action( 'before_run_rocket_bot', $spider, $start_url );

	wp_remote_get( WP_ROCKET_BOT_URL.'?spider=' . $spider . '&start_url=' . $start_url );

	/**
	 * Fires after WP Rocket Bot was called
	 *
	 * @since 1.1.0
	 * @param string $spider The spider name
	 * @param string $start_url URL that crawl by the bot
	*/
	do_action( 'after_run_rocket_bot', $spider, $start_url );

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
	if ( rocket_is_plugin_active('sitepress-multilingual-cms/sitepress.php') ) {
		
		$url = $GLOBALS['sitepress']->language_url( $lang );
		
	} elseif ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		
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
	foreach ( $langs as $lang ) {
		run_rocket_bot( 'cache-preload', $lang );
	}

}