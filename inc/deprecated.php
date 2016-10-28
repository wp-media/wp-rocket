<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/*
 * Deprecated functions come here to die.
 */


if ( ! function_exists( 'get_rocket_pages_not_cached' ) ) :
/**
 * Get all pages we don't cache (string)
 *
 * @since 1.0
 * @deprecated 2.0
 * @deprecated Use get_rocket_cache_reject_uri()
 *
 */
function get_rocket_pages_not_cached() {
	_deprecated_function( __FUNCTION__, '2.0', "get_rocket_cache_reject_uri()" );
	return get_rocket_cache_reject_uri();
}
endif;

if ( ! function_exists( 'get_rocket_cookies_not_cached' ) ) :
/**
 * Get all cookie names we don't cache (string)
 *
 * @since 1.0
 * @deprecated 2.0
 * @deprecated Use get_rocket_cache_reject_cookies()
 *
 */
function get_rocket_cookies_not_cached() {
	_deprecated_function( __FUNCTION__, '2.0', "get_rocket_cache_reject_cookies()" );
	return get_rocket_cache_reject_cookies();
}
endif;

if ( ! function_exists( 'get_rocket_cron_interval' ) ) :
/**
 * Get the interval task cron purge in seconds
 * This setting can be changed from the options page of the plugin
 *
 * @since 1.0
 * @deprecated 2.0
 * @deprecated Use get_rocket_purge_cron_interval()
 *
 */
function get_rocket_cron_interval() {
	_deprecated_function( __FUNCTION__, '2.0', "get_rocket_purge_cron_interval()" );
	return get_rocket_purge_cron_interval();
}
endif;

if ( ! function_exists( 'run_rocket_bot_for_all_langs' ) ) :
/**
 * Launch the Cache Preload Robot for all active langs
 *
 * @since 2.0
 * @deprecated 2.2
 * @deprecated Use run_rocket_bot()
 *
 */
function run_rocket_bot_for_all_langs() {
	_deprecated_function( __FUNCTION__, '2.2', "run_rocket_bot()" );
	return run_rocket_bot( 'cache-preload' );
}
endif;

if ( ! function_exists( 'run_rocket_bot_for_selected_lang' ) ) :
/**
 * Launch the Cache Preload Robot for a selected lang
 *
 * @since 2.0
 * @deprecated 2.2
 * @deprecated Use run_rocket_bot()
 *
 */
function run_rocket_bot_for_selected_lang( $lang ) {
	_deprecated_function( __FUNCTION__, '2.2', "run_rocket_bot()" );
	return run_rocket_bot( 'cache-preload', $lang );
}
endif;

if ( ! function_exists( 'get_rocket_home_url' ) ) :
/**
 * Returns a full and correct home_url without subdmain, see rocket_get_domain()
 *
 * @since 1.0
 * @deprecated 2.2
 *
 */
function get_rocket_home_url( $url = null ) {
	_deprecated_function( __FUNCTION__, '2.2' );
	return false;
}
endif;

if ( ! function_exists( 'rocket_has_translation_plugin_active' ) ) :
/**
 * Check if a translation plugin is activated (WPML or qTranslate)
 *
 * @since 2.0
 * @deprecated 2.2
 * @deprecated Use rocket_has_i18n()
 *
 */
function rocket_has_translation_plugin_active() {
	_deprecated_function( __FUNCTION__, '2.2', 'rocket_has_i18n()' );
	return rocket_has_i18n();
}
endif;

if ( ! function_exists( 'get_rocket_all_active_langs' ) ) :
/**
 * Get URI all of active languages
 *
 * @since 2.0
 * @deprecated 2.2
 * @deprecated Use get_rocket_i18n_code()
 *
 */
function get_rocket_all_active_langs() {
	_deprecated_function( __FUNCTION__, '2.2', 'get_rocket_i18n_code()' );
	return get_rocket_i18n_code();
}
endif;

if ( ! function_exists( 'get_rocket_all_active_langs_uri' ) ) :
/**
 * Get URI all of active languages
 *
 * @since 2.0
 * @deprecated 2.2
 * @deprecated Use get_rocket_i18n_uri()
 *
 */
function get_rocket_all_active_langs_uri() {
	_deprecated_function( __FUNCTION__, '2.2', 'get_rocket_i18n_uri()' );
	return get_rocket_i18n_uri();
}
endif;

if ( ! function_exists( 'get_rocket_parse_url_for_lang' ) ) :
/**
 * Extract and return host, path and scheme for a specific lang
 *
 * @since 2.0
 * @deprecated 2.2
 * @deprecated Use get_rocket_parse_url()
 *
 */
function get_rocket_parse_url_for_lang( $lang ) {
	_deprecated_function( __FUNCTION__, '2.2', 'get_rocket_parse_url()' );
	return get_rocket_parse_url( get_rocket_i18n_home_url( $lang ) );
}
endif;

if ( ! function_exists( 'rocket_clean_domain_for_selected_lang' ) ) :
/**
 * Remove only cache files of selected lang
 *
 * @since 2.0
 * @deprecated 2.2
 * @deprecated Use rocket_clean_domain()
 *
 */
function rocket_clean_domain_for_selected_lang( $lang ) {
	_deprecated_function( __FUNCTION__, '2.2', 'rocket_clean_domain()' );
	return rocket_clean_domain( $lang );
}
endif;

if ( ! function_exists( 'rocket_clean_domain_for_all_langs' ) ) :
/**
 * Remove cache files of all langs
 *
 * @since 2.0
 * @deprecated 2.2
 * @deprecated Use rocket_clean_domain()
 *
 */
function rocket_clean_domain_for_all_langs() {
	_deprecated_function( __FUNCTION__, '2.2', 'rocket_clean_domain()' );
	return rocket_clean_domain();
}
endif;

if ( ! function_exists( 'get_rocket_langs_to_preserve' ) ) :
/**
 * Get folder paths to preserve languages ​​when purging a domain
 * This function is required when the domains of languages (​​other than the default) are managed by subfolders
 * By default, when you clear the cache of the french website with the domain example.com, all subdirectory like /en/ and /de/ are deleted.
 * But, if you have a domain for your english and german websites with example.com/en/ and example.com/de/, you want to keep the /en/ and /de/ directory when the french domain is cleared.
 *
 * @since 2.0
 * @deprecated 2.2
 * @deprecated Use get_rocket_i18n_to_preserve()
 *
 */
function get_rocket_langs_to_preserve( $current_lang ) {
	_deprecated_function( __FUNCTION__, '2.2', 'get_rocket_i18n_to_preserve()' );
	return get_rocket_i18n_to_preserve( $current_lang );
}
endif;

if ( ! function_exists( 'get_rocket_subdomains_langs' ) ) :
/**
 * Get subdomains URL of all languages
 *
 * @since 2.0
 * @deprecated 2.2
 * @deprecated Use get_rocket_i18n_subdomains()
 *
 */
function get_rocket_subdomains_langs() {
	_deprecated_function( __FUNCTION__, '2.2', 'get_rocket_i18n_subdomains()' );
	return get_rocket_i18n_subdomains();
}
endif;

if ( ! function_exists( 'rocket_replace_domain_mapping_siteurl' ) ) :
/**
 * Get Domain Mapping host based on original URL
 *
 * @since 2.2
 * @deprecated 2.6.5
 *
 */
function rocket_replace_domain_mapping_siteurl( $url = null ) {
	_deprecated_function( __FUNCTION__, '2.6.5' );
	return false;
}
endif;

if ( ! function_exists( 'rocket_sanitize_cookie' ) ) :
/**
 * Used to sanitize values of the "Don't cache pages that use the following cookies" option.
 *
 * @since 2.6.4
 * @deprecated 2.7
 * @deprecated Use rocket_sanitize_key()
 *
 */
function rocket_sanitize_cookie( $cookie ) {
	_deprecated_function( __FUNCTION__, '2.7', 'rocket_sanitize_key()' );
	return rocket_sanitize_key( $cookie );
}
endif;

if ( ! function_exists( 'set_rocket_cloudflare_async' ) ) :
/**
 * Used to set the CloudFlare Rocket Loader value
 *
 * @since 2.5
 * @deprecated 2.8.16
 * @deprecated Use set_rocket_cloudflare_rocket_loader()
 *
 */
function set_rocket_cloudflare_async( $cf_rocket_loader ) {
    _deprecated_function( __FUNCTION__, '2.8.16', 'set_rocket_cloudflare_rocket_loader()' );
    return set_rocket_cloudflare_rocket_loader( $cf_rocket_loader );
}
endif;

if ( ! function_exists( 'set_rocket_cloudflare_cache_lvl' ) ) :
/**
 * Used to set the CloudFlare cache level
 *
 * @since 2.5
 * @deprecated 2.8.16
 * @deprecated Use set_rocket_cloudflare_cache_level()
 *
 */
function set_rocket_cloudflare_cache_lvl( $cf_cache_level ) {
    _deprecated_function( __FUNCTION__, '2.8.16', 'set_rocket_cloudflare_cache_level()' );
    return set_rocket_cloudflare_cache_level( $cf_cache_level );
}
endif;

if ( ! function_exists( 'rocket_delete_script_wp_version' ) ) :
/**
 * Used to remove version query string in CSS/JS URL
 *
 * @since 1.1.6
 * @deprecated 2.9
 * @deprecated Use rocket_browser_cache_busting()
 *
 */
function rocket_delete_script_wp_version( $src ) {
    _deprecated_function( __FUNCTION__, '2.9', 'rocket_browser_cache_busting()' );
    return rocket_browser_cache_busting( $src );
}
endif;