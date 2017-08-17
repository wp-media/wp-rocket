<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

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
endif;
