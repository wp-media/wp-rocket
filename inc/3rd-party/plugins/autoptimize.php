<?php

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'autoptimizeCache' ) ) :
	/**
	 * Deactivate WP Rocket lazyload if Autoptimize lazyload is enabled
	 *
	 * @since 3.3.4
	 *
	 * @param string $old_value Previous autoptimize option value.
	 * @param string $value New autoptimize option value.
	 * @return void
	 */
	function rocket_maybe_deactivate_lazyload( $old_value, $value ) {
		if ( empty( $old_value['autoptimize_imgopt_checkbox_field_3'] ) && ! empty( $value['autoptimize_imgopt_checkbox_field_3'] ) ) {
			update_rocket_option( 'lazyload', 0 );
		}
	}
	add_action( 'update_option_autoptimize_imgopt_settings', 'rocket_maybe_deactivate_lazyload', 10, 2 );

	/**
	 * Improvement with Autoptimize: clear the cache when Autoptimize's cache is cleared
	 *
	 * @since 2.7
	 */
	add_action( 'autoptimize_action_cachepurged', 'rocket_clean_domain' );
endif;

if ( class_exists( 'autoptimizeConfig' ) ) :
	/**
	 * Deactivate WP Rocket CSS Minification if Autoptimize CSS minification is enabled
	 *
	 * @since 2.9.5
	 * @author Remy Perona
	 *
	 * @param string $old_value Previous autoptimize option value.
	 * @param string $value New autoptimize option value.
	 */
	function rocket_maybe_deactivate_minify_css( $old_value, $value ) {
		if ( $value !== $old_value && 'on' === $value ) {
			update_rocket_option( 'minify_css', 0 );
		}
	}
	add_action( 'update_option_autoptimize_css', 'rocket_maybe_deactivate_minify_css', 10, 2 );

	/**
	 * Deactivate WP Rocket JS Minification if Autoptimize JS minification is enabled
	 *
	 * @since 2.9.5
	 * @author Remy Perona
	 *
	 * @param string $old_value Previous autoptimize option value.
	 * @param string $value New autoptimize option value.
	 */
	function rocket_maybe_deactivate_minify_js( $old_value, $value ) {
		if ( $value !== $old_value && 'on' === $value ) {
			update_rocket_option( 'minify_js', 0 );
			update_rocket_option( 'minify_concatenate_js', 0 );
		}
	}
	add_action( 'update_option_autoptimize_js', 'rocket_maybe_deactivate_minify_js', 10, 2 );

	/**
	 * Deactivate WP Rocket async CSS if Autoptimize async CSS is enabled
	 *
	 * @since 2.10
	 * @author Remy Perona
	 *
	 * @param string $old_value Previous autoptimize option value.
	 * @param string $value New autoptimize option value.
	 */
	function rocket_maybe_deactivate_css_defer( $old_value, $value ) {
		if ( $value !== $old_value && 'on' === $value ) {
			update_rocket_option( 'autoptimize_css_defer', 0 );
		}
	}
	add_action( 'update_option_autoptimize_css_defer', 'rocket_maybe_deactivate_css_defer', 10, 2 );

endif;

/**
 * Disable WP Rocket minification options when activating Autoptimize and values are already in the database.
 *
 * @since 2.9.5
 * @author Remy Perona
 */
function rocket_activate_autoptimize() {
	if ( 'on' === get_option( 'autoptimize_css' ) ) {
		update_rocket_option( 'minify_css', 0 );
	}

	if ( 'on' === get_option( 'autoptimize_js' ) ) {
		update_rocket_option( 'minify_js', 0 );
		update_rocket_option( 'minify_concatenate_js', 0 );
	}

	if ( 'on' === get_option( 'autoptimize_css_defer' ) ) {
		update_rocket_option( 'async_css', 0 );
	}

	$lazyload = get_option( 'autoptimize_imgopt_settings' );

	if ( ! empty( $lazyload['autoptimize_imgopt_checkbox_field_3'] ) ) {
		update_rocket_option( 'lazyload', 0 );
	}
}
add_action( 'activate_autoptimize/autoptimize.php', 'rocket_activate_autoptimize', 11 );

/**
 * Disable WP Rocket minification if Autoptimize css /js minification is enabled.
 *
 * @param array $options WP Rocket options array.
 *
 * @since 3.18
 *
 * @return array
 */
function rocket_maybe_disable_minification( $options ) {
	// Bail early if plugin is not active.
	if ( ! is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
		return $options;
	}

	if ( 'on' === get_option( 'autoptimize_css' ) ) {
		$options['minify_css'] = 0;
	}

	if ( 'on' === get_option( 'autoptimize_js' ) ) {
		$options['minify_js'] = 0;
	}

	return $options;
}

add_filter( 'rocket_first_install_options', 'rocket_maybe_disable_minification' );

/**
 * Disable WP Rocket lazyload fields if Autoptimize lazyload is enabled
 *
 * @since 3.3.4
 *
 * @return bool
 */
function rocket_maybe_disable_lazyload() {
	$lazyload = get_option( 'autoptimize_imgopt_settings' );

	if ( is_plugin_active( 'autoptimize/autoptimize.php' ) && ! empty( $lazyload['autoptimize_imgopt_checkbox_field_3'] ) ) {
		return true;
	}

	return false;
}

/**
 * Disable WP Rocket CSS minification field if Autoptimize CSS minification is enabled
 *
 * @since 2.9.5
 * @author Remy Perona
 *
 * @return bool|null True if it is active
 */
function rocket_maybe_disable_minify_css() {
	if ( is_plugin_active( 'autoptimize/autoptimize.php' ) && 'on' === get_option( 'autoptimize_css' ) ) {
		return true;
	}
}

/**
 * Disable WP Rocket JS minification field if Autoptimize JS minification is enabled
 *
 * @since 2.9.5
 * @author Remy Perona
 *
 * @return bool|null True if it is active
 */
function rocket_maybe_disable_minify_js() {
	if ( is_plugin_active( 'autoptimize/autoptimize.php' ) && 'on' === get_option( 'autoptimize_js' ) ) {
		return true;
	}
}

/**
 * Disable WP Rocket async CSS field if Autoptimize async CSS is enabled
 *
 * @since 2.10
 * @author Remy Perona
 *
 * @return bool|null True if it is active
 */
function rocket_maybe_disable_async_css() {
	if ( is_plugin_active( 'autoptimize/autoptimize.php' ) && 'on' === get_option( 'autoptimize_css_defer' ) ) {
		return true;
	}
}
