<?php

if ( class_exists( 'WR2X_Admin' ) ) :
	add_filter( 'wr2x_img_retina_url', 'rocket_cdn_on_images_from_wp_retina_x2' );
	add_filter( 'wr2x_img_url', 'rocket_cdn_on_images_from_wp_retina_x2' );
	/**
	 * Conflict with WP Retina x2: Apply CDN on srcset attribute.
	 *
	 * @since 2.9.1 Use global $wr2x_admin
	 * @since 2.5.5
	 *
	 * @param string $url URL of the image.
	 * @return string Updated URL with CDN
	 */
	function rocket_cdn_on_images_from_wp_retina_x2( $url ) {
		global $wr2x_admin;

		if ( ! method_exists( $wr2x_admin, 'is_pro' ) || ! $wr2x_admin->is_pro() ) {
			return $url;
		}
		
		$cdn_domain = get_option( 'wr2x_cdn_domain' );

		if ( ! empty( $cdn_domain ) ) {
			return $url;
		}
		
		return get_rocket_cdn_url( $url, array( 'all', 'images' ) );
	}
endif;
