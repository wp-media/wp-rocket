<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( function_exists( 'autoptimize_do_cachepurged_action' ) ) :

	/**
	 * Improvement with Autoptimize: clear the cache when Autoptimize's cache is cleared
	 *
	 * @since 2.7
	 */
	add_action( 'autoptimize_action_cachepurged', 'rocket_clean_domain' );

endif;

if ( class_exists( 'autoptimizeConfig' ) ) :
	/**
	 * Deactivate WP Rocket HTML Minification if Autoptimize HTML minification is enabled
	 *
	 * @since 2.9.5
	 * @author Remy Perona
	 *
	 * @param string $old_value Previous autoptimize option value.
	 * @param string $value New autoptimize option value.
	 */
	function rocket_maybe_deactivate_minify_html( $old_value, $value ) {
		if ( $value !== $old_value && $value === 'on' ) {
			update_rocket_option( 'minify_html', 0 );
			update_rocket_option( 'minify_html_inline_css', 0 );
			update_rocket_option( 'minify_html_inline_js', 0 );
		}
	}
	add_action( 'update_option_autoptimize_html', 'rocket_maybe_deactivate_minify_html', 10, 2 );

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
		if ( $value !== $old_value && $value === 'on' ) {
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
		if ( $value !== $old_value && $value === 'on' ) {
			update_rocket_option( 'minify_js', 0 );
		}
	}
	add_action( 'update_option_autoptimize_js', 'rocket_maybe_deactivate_minify_js', 10, 2 );

endif;

/**
 * Disable WP Rocket minification options when activating Autoptimize and values are already in the database.
 *
 * @since 2.9.5
 * @author Remy Perona
 */
function rocket_activate_autoptimize() {
	if ( 'on' === get_option( 'autoptimize_html') ) {
		update_rocket_option( 'minify_html', 0 );
		update_rocket_option( 'minify_html_inline_css', 0 );
		update_rocket_option( 'minify_html_inline_js', 0 );
	}
	
	if ( 'on' === get_option( 'autoptimize_css') ) {
		update_rocket_option( 'minify_css', 0 );
	}
	
	if ( 'on' === get_option( 'autoptimize_js') ) {
		update_rocket_option( 'minify_js', 0 );
	}
}
add_action( 'activate_autoptimize/autoptimize.php', 'rocket_activate_autoptimize', 11 );

/**
 * Disable WP Rocket HTML minification field if Autoptimize HTML minification is enabled
 *
 * @since 2.9.5
 * @author Remy Perona
 *
 * @return bool|null True if it is active
 */
function rocket_maybe_disable_minify_html() {
	if ( is_plugin_active( 'autoptimize/autoptimize.php' ) && 'on' === get_option( 'autoptimize_html') ) {
		return true;
	}
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
	if ( is_plugin_active( 'autoptimize/autoptimize.php' ) && 'on' === get_option( 'autoptimize_css') ) {
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
	if ( is_plugin_active( 'autoptimize/autoptimize.php' ) && 'on' === get_option( 'autoptimize_js') ) {
		return true;
	}
}
