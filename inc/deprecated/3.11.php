<?php

defined( 'ABSPATH' ) || exit;

/**
 * Deactivate WP Rocket lazyload if Autoptimize lazyload is enabled
 *
 * @since 3.11 deprecated
 * @since 3.3.4
 *
 * @param string $old_value Previous autoptimize option value.
 * @param string $value New autoptimize option value.
 * @return void
 */
function rocket_maybe_deactivate_lazyload( $old_value, $value ) {
	_deprecated_function( __FUNCTION__ . '()', '3.11' );

	if ( empty( $old_value['autoptimize_imgopt_checkbox_field_3'] ) && ! empty( $value['autoptimize_imgopt_checkbox_field_3'] ) ) {
		update_rocket_option( 'lazyload', 0 );
		update_rocket_option( 'lazyload_iframes', 0 );
		update_rocket_option( 'lazyload_youtube', 0 );
	}
}

/**
 * Disable WP Rocket lazyload fields if Autoptimize lazyload is enabled
 *
 * @since 3.11 deprecated
 * @since 3.3.4
 *
 * @return bool
 */
function rocket_maybe_disable_lazyload() {
	_deprecated_function( __FUNCTION__ . '()', '3.11' );

	$lazyload = get_option( 'autoptimize_imgopt_settings' );

	if ( is_plugin_active( 'autoptimize/autoptimize.php' ) && ! empty( $lazyload['autoptimize_imgopt_checkbox_field_3'] ) ) {
		return true;
	}

	return false;
}
