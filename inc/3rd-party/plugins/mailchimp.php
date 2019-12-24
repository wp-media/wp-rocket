<?php

defined( 'ABSPATH' ) || exit;

/**
 * Conflict with MailChimp List Subscribe Form: Enqueue style without lack of performance, grrrr!!!
 *
 * @since 2.6
 */
function rocket_fix_mailchimp_main_css() {
	if ( ! defined( 'MCSF_VER' ) || ! function_exists( 'mailchimpSF_main_css' ) ) {
		return;
	}

	$blog_id    = get_current_blog_id();
	$cache_path = WP_ROCKET_MINIFY_CACHE_PATH . $blog_id . '/';
	$cache_url  = WP_ROCKET_MINIFY_CACHE_URL . $blog_id . '/';
	$css_path   = $cache_path . 'mailchimpSF_main_css.css';

	if ( ! rocket_direct_filesystem()->is_dir( $cache_path ) ) {
		rocket_mkdir_p( $cache_path );
	}

	if ( ! rocket_direct_filesystem()->exists( $css_path ) ) {
		ob_start();
		mailchimpSF_main_css();
		$content = ob_get_contents();
		ob_end_clean();

		rocket_put_content( $css_path, $content );
	}

	wp_deregister_style( 'mailchimpSF_main_css' );
	wp_register_style( 'mailchimpSF_main_css', $cache_url . 'mailchimpSF_main_css.css', null, MCSF_VER );
}
add_action( 'init', 'rocket_fix_mailchimp_main_css', PHP_INT_MAX );
