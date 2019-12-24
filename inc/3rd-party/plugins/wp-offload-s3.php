<?php

defined( 'ABSPATH' ) || exit;

if ( is_admin() && ( function_exists( 'as3cf_init' ) || function_exists( 'as3cf_pro_init' ) ) ) :
	add_action( 'aws_init', 'rocket_as3cf_compatibility', 12 );
endif;


/**
 * Compatibility with WP Offload S3.
 *
 * @since 2.10.7
 * @author Remy Perona
 */
function rocket_as3cf_compatibility() {
	global $as3cf;

	if ( isset( $as3cf ) && $as3cf->is_plugin_setup() && 1 === (int) $as3cf->get_setting( 'serve-from-s3' ) ) {
			// Remove images option from WP Rocket CDN dropdown settings.
			add_filter( 'rocket_allow_cdn_images', '__return_false' );
	}
}
