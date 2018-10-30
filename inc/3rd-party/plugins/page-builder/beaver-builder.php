<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( defined( 'FL_BUILDER_VERSION' ) ) :
	/**
	 * Purge the cache when the beaver builder layout is updated to update the minified files content & URL
	 *
	 * @since 2.9 Also clear the cache busting folder
	 * @since 2.8.6
	 */
	function rocket_beaver_builder_clean_domain() {
		rocket_clean_minify();
		rocket_clean_domain();
		rocket_clean_cache_busting();
	}
	add_action( 'fl_builder_before_save_layout', 'rocket_beaver_builder_clean_domain', 10, 4 );
	add_action( 'fl_builder_cache_cleared', 'rocket_beaver_builder_clean_domain' );

	/**
	 * Apply CDN settings to Beaver Builder parallax.
	 *
	 * @since  3.2.1
	 * @author Grégory Viguier
	 *
	 * @param  array $attrs HTML attributes.
	 * @return array
	 */
	function rocket_beaver_builder_add_cdn_to_parallax( $attrs ) {
		if ( ! empty( $attrs['data-parallax-image'] ) ) {
			$attrs['data-parallax-image'] = get_rocket_cdn_url( $attrs['data-parallax-image'], [ 'all', 'images' ] );
		}

		return $attrs;
	}
	add_filter( 'fl_builder_row_attributes', 'rocket_beaver_builder_add_cdn_to_parallax' );
endif;
