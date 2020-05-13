<?php

defined( 'ABSPATH' ) || exit;

if ( get_rocket_option( 'emoji', 0 ) ) {
	/**
	 * Disable the emoji functionality to reduce then number of external HTTP requests.
	 *
	 * @since 2.7
	 */
	function rocket_disable_emoji() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}
	add_action( 'init', 'rocket_disable_emoji' );

	/**
	 * Remove the tinymce emoji plugin.
	 *
	 * @since 2.7
	 *
	 * @param Array $plugins Plugins loaded for TinyMCE.
	 */
	function rocket_disable_emoji_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, [ 'wpemoji' ] );
		}

		return [];
	}
	add_filter( 'tiny_mce_plugins', 'rocket_disable_emoji_tinymce' );
}
