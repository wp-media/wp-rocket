<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Check whether the plugin is active by checking the active_plugins list.
 *
 * @since 1.3.0
 * @source wp-admin/includes/plugin.php
 */
function rocket_is_plugin_active( $plugin )
{
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || rocket_is_plugin_active_for_network( $plugin );
}

/**
 * Check whether the plugin is active for the entire network.
 *
 * @since 1.3.0
 * @source wp-admin/includes/plugin.php
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
 * Check if a translation plugin is activated
 *
 * @since 2.0
 * @access public
 * @return bool
 */
function rocket_has_i18n()
{

	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' )  // WPML
		|| rocket_is_plugin_active( 'qtranslate/qtranslate.php' )  				// qTranslate
		|| rocket_is_plugin_active( 'polylang/polylang.php' ) ) { 				// Polylang
		return true;
	}

	return false;
}

/**
 * Get infos of all active languages
 *
 * @since 2.0
 * @access public
 * @return array List of language code
 */
function get_rocket_i18n_code()
{

	if( ! rocket_has_i18n() ) {
		return false;
	}

	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		return array_keys( $GLOBALS['sitepress']->get_active_languages() );
	}

	if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		return $GLOBALS['q_config']['enabled_languages'];
	}

	if ( rocket_is_plugin_active( 'polylang/polylang.php' ) ) {
		return wp_list_pluck( $GLOBALS['polylang']->model->get_languages_list(), 'slug' );
	}

}

/**
 * Get URI all of active languages
 *
 * @since 2.0
 * @access public
 * @return array $urls
 */
function get_rocket_i18n_uri()
{

	$urls = array();

	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {

		$langs = get_rocket_i18n_code();
		foreach ( $langs as $lang ) {
			$urls[] = $GLOBALS['sitepress']->language_url( $lang );
		}

	} else if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {

		$langs = get_rocket_i18n_code();
		foreach ( $langs as $lang ) {
			$urls[] = qtrans_convertURL( home_url(), $lang, true );
		}

	} else if ( 'polylang/polylang.php' ) {
		$urls = wp_list_pluck( $GLOBALS['polylang']->model->get_languages_list(), 'home_url' );
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
 * @access public
 * @param string $current_lang
 * @return array $langs_to_preserve
 */
function get_rocket_i18n_to_preserve( $current_lang )
{

	$langs_to_preserve = array();
	if ( ! rocket_has_i18n() ) {
		return $langs_to_preserve;
	}

	$langs = get_rocket_i18n_code();

	// Unset current lang to the preserve dirs
	$langs = array_flip( $langs );
	unset( $langs[$current_lang] );
	$langs = array_flip( $langs );

	// Stock all URLs of langs to preserve
	foreach( $langs as $lang ) {
		list( $host, $path ) = get_rocket_parse_url( get_rocket_i18n_home_url( $lang ) );
		$langs_to_preserve[] = WP_ROCKET_CACHE_PATH . $host . '(.*)/' . trim( $path, '/' );
	}

	$langs_to_preserve = apply_filters( 'rocket_langs_to_preserve', $langs_to_preserve );
	return $langs_to_preserve;

}

/**
 * Get subdomains URL of all languages
 *
 * @since 2.1
 * @access public
 * @return array $urls
 */
function get_rocket_i18n_subdomains()
{

	if ( ! rocket_has_i18n() ) {
		return false;
	}

	$urls = array();
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		$option = get_option( 'icl_sitepress_settings' );
		if ( (int) $option['language_negotiation_type'] == 2 ) {
			$urls = get_rocket_i18n_uri();
		}
	} else if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		if( (int) $GLOBALS['q_config']['url_mode'] == 3 ) {
			$urls = get_rocket_i18n_uri();
		}
	} else if ( rocket_is_plugin_active( 'polylang/polylang.php' ) ) {
		if ( (int) $GLOBALS['polylang']->options['force_lang'] == 2 ) {
			$urls = get_rocket_i18n_uri();
		}
	}

	return $urls;
}

/**
 * Get home URL of a specific lang
 *
 * @since 2.2
 * @access public
 * @param string $lang (default: '') The language code
 * @return string $url
 */
function get_rocket_i18n_home_url( $lang = '' ) {

	$url = home_url();
	if ( ! rocket_has_i18n() ) {
		return $url;
	}

	if ( rocket_is_plugin_active('sitepress-multilingual-cms/sitepress.php') ) {
		$url = $GLOBALS['sitepress']->language_url( $lang );
	} else if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		$url = qtrans_convertURL( home_url(), $lang, true );
	} else if ( rocket_is_plugin_active( 'polylang/polylang.php' ) ) {
		$url = pll_home_url( $lang );
	}

	return $url;
}