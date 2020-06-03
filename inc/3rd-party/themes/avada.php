<?php

defined( 'ABSPATH' ) || exit;

$current_theme = wp_get_theme(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

if ( 'Avada' === $current_theme->get( 'Name' ) ) {
	// When Avada theme purge its own cache.
	add_action( 'avada_clear_dynamic_css_cache',  'rocket_clean_domain' );

	/**
	 * Deactivate WP Rocket lazyload if Avada lazyload is enabled
	 *
	 * @since 3.3.4
	 * @author Remy Perona
	 *
	 * @param string $old_value Previous Avada option value.
	 * @param string $value New Avada option value.
	 * @return void
	 */
	function rocket_avada_maybe_deactivate_lazyload( $old_value, $value ) {
		if ( empty( $old_value['lazy_load'] ) && ! empty( $value['lazy_load'] ) ) {
			update_rocket_option( 'lazyload', 0 );
		}
	}
	add_action( 'update_option_fusion_options', 'rocket_avada_maybe_deactivate_lazyload', 10, 2 );
}

/**
 * Disable WP Rocket lazyload field if Avada lazyload is enabled
 *
 * @since 3.3.4
 * @author Remy Perona
 *
 * @return bool
 */
function rocket_avada_maybe_disable_lazyload() {
	$avada_options = get_option( 'fusion_options' );
	$current_theme = wp_get_theme();

	if ( 'Avada' === $current_theme->get( 'Name' ) && ! empty( $avada_options['lazy_load'] ) ) {
		return true;
	}

	return false;
}

/**
 * Clears WP Rocket's cache after Avada's Fusion Patcher flushes their caches
 *
 * @since 3.3.5
 * @author Vasilis Manthos
 */
function rocket_avada_clear_cache_fusion_patcher() {
	rocket_clean_domain();
}
add_action( 'fusion_cache_reset_after', 'rocket_avada_clear_cache_fusion_patcher' );
