<?php

defined( 'ABSPATH' ) || exit;

use WP_Rocket\Engine\Preload\FullProcess;
use WP_Rocket\Engine\Preload\Homepage;
use WP_Rocket\Engine\Preload\Sitemap;

/**
 * Launches the Homepage preload (helper function for backward compatibility)
 *
 * @since 2.6.4 Don't preload localhost & .dev domains
 * @since 1.0
 *
 * @param string $spider (default: 'cache-preload') The spider name: cache-preload or cache-json.
 * @param string $lang (default: '') The language code to preload.
 *
 * @return bool Status of preload.
 */
function run_rocket_bot( $spider = 'cache-preload', $lang = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	if ( ! get_rocket_option( 'manual_preload' ) ) {
		return false;
	}

	$urls = [];

	if ( ! $lang ) {
		$urls = get_rocket_i18n_uri();
	} else {
		$urls[] = get_rocket_i18n_home_url( $lang );
	}

	$container = apply_filters( 'rocket_container', null );

	if ( ! $container ) {
		return false;
	}

	$controller = $container->get( 'preload_clean_controller' );

	$controller->partial_clean( $urls );

	return true;
}

/**
 * Launches the sitemap preload (helper function for backward compatibility)
 *
 * @since 2.8
 * @author Remy Perona
 *
 * @return void
 */
function run_rocket_sitemap_preload() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	if ( ! get_rocket_option( 'manual_preload' ) ) {
		return;
	}

	$container = apply_filters( 'rocket_container', null );

	if ( ! $container ) {
		return;
	}

	$controller = $container->get( 'load_initial_sitemap_controller' );

	$controller->load_initial_sitemap();
}

