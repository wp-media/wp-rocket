<?php

if ( class_exists( 'WR2X_Admin' ) ) :
	/**
	 * Conflict with WP Retina x2: Apply CDN on srcset attribute.
	 *
	 * @since 2.9.1 Use global $wr2x_admin
	 * @since 2.5.5
	 *
	 * @param string $url URL of the image.
	 * @param string Updated URL with CDN
	 */
	add_filter( 'wr2x_img_retina_url', 'rocket_cdn_on_images_from_wp_retina_x2' );
	add_filter( 'wr2x_img_url', 'rocket_cdn_on_images_from_wp_retina_x2' );
	function rocket_cdn_on_images_from_wp_retina_x2( $url ) {
		global $wr2x_admin;
		if ( $wr2x_admin->is_pro() ) {
			$cdn_domain = wr2x_getoption( 'cdn_domain', 'wr2x_advanced', '' );
		}
		
		if ( empty( $cdn_domain ) ) {
			return get_rocket_cdn_url( $url, array( 'all', 'images' ) );
		}
		
		return $url;
	}
endif;
