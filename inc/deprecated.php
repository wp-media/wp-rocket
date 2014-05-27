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
 *
 */
function rocket_has_translation_plugin_active() {
	_deprecated_function( __FUNCTION__, '2.2', 'rocket_has_i18n()' );
	return rocket_has_i18n();
}
endif;

if ( ! function_exists( 'get_rocket_all_active_langs_uri' ) ) :
/**
 * Get URI all of active languages
 *
 * @since 2.0
 * @deprecated 2.2
 *
 */
function get_rocket_all_active_langs_uri() {
	_deprecated_function( __FUNCTION__, '2.2', 'get_rocket_i18n_uri()' );
	return get_rocket_i18n_uri();
}
endif;