<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


/**
 * Check whether the plugin is active by checking the active_plugins list.
 *
 * @since 1.3.0
 * @source : wp-admin/includes/plugin.php
 *
 */

function rocket_is_plugin_active( $plugin )
{
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || rocket_is_plugin_active_for_network( $plugin );
}



/**
 * Check whether the plugin is active for the entire network.
 *
 * @since 1.3.0
 * @source : wp-admin/includes/plugin.php
 *
 */

function rocket_is_plugin_active_for_network( $plugin )
{
	if ( !is_multisite() ) {
		return false;
	}

	$plugins = get_site_option( 'active_sitewide_plugins');
	if ( isset($plugins[$plugin]) ) {
		return true;
	}

	return false;

}



/**
 * Check if a translation plugin is activated (WPML or qTranslate)
 *
 * @since 2.0
 *
 */

function rocket_has_translation_plugin_active()
{

	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) // WPML
		|| rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) { // qTranslate 
		return true;
	}

	return false;
}



/**
 * Get infos of all active languages
 *
 * @since 2.0
 *
 */

function get_rocket_all_active_langs()
{

	// WPML
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		global $sitepress;
		return $sitepress->get_active_languages();
	}

	// qTranslate
	if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		global $q_config;
		return $q_config['enabled_languages'];
	}

	return false;
}



/**
 * Get URI all of active languages
 *
 * @since 2.0
 *
 */

function get_rocket_all_active_langs_uri()
{

	$urls  = array();
	$langs = get_rocket_all_active_langs();

	// WPML
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {

		global $sitepress;
		foreach ( array_keys( $langs ) as $lang ) {
			$urls[] = $sitepress->language_url( $lang );
		}

	} 
	// qTranslate
	elseif ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) { 

		foreach ( $langs as $lang ) {
			$urls[] = qtrans_convertURL( home_url(), $lang, true );
		}

	}

	return $urls;
}



/**
 * Get folder paths to preserve languages ​​when purging a domain
 * This function is required when the domains of languages (​​other than the default) are managed by subfolders
 * By default, when you clear the cache of the french website with the domain example.com, all subdirectory like /en/ and /de/ are deleted.
 * But, if you have a domain for your english and german websites with example.com/en/ and example.com/de/, you want to keep the /en/ and /de/ directory when the french domain is cleared.
 *
 * @since 2.0
 *
 */

function get_rocket_langs_to_preserve( $current_lang )
{

	$langs = get_rocket_all_active_langs();
	$langs_to_preserve = array();

	// Unset current lang to the preserve dirs

	// WPML
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		unset( $langs[$current_lang] );
		$langs = array_keys( $langs );
	}

	// qTranslate
	if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		$langs = array_flip( $langs );
		unset( $langs[$current_lang] );
		$langs = array_flip( $langs );
	}

	// Stock all URLs of langs to preserve
	foreach ( $langs as $lang ) {
		list( $host, $path ) = get_rocket_parse_url_for_lang( $lang );
		$langs_to_preserve[] = WP_ROCKET_CACHE_PATH . $host . '(.*)/' . trim( $path, '/' );
	}

	$langs_to_preserve = apply_filters( 'rocket_langs_to_preserve', $langs_to_preserve );
	return $langs_to_preserve;

}


/**
 * Get subdomains URL of all languages
 *
 * @since 2.1
 *
 */

function get_rocket_subdomains_langs()
{

	// Check if a translation plugin is activated
	if ( ! rocket_has_translation_plugin_active() ) {
		return false;
	}

	$urls = array();

	// WPML
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {

		$option = get_option( 'icl_sitepress_settings' );

		// Check if WPML set to serve subdomains URL
		if ( (int) $option['language_negotiation_type'] == 2 ) {
			$urls = get_rocket_all_active_langs_uri();
		}

	}

	// qTranslate
	if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {

		global $q_config;

		// Check if qTranslate set to serve subdomains URL
		if( (int) $q_config['url_mode'] == 3 ) {
			$urls = get_rocket_all_active_langs_uri();
		}

	}
	
	return $urls;
}