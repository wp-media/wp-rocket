<?php

defined( 'ABSPATH' ) || exit;

if ( defined( 'ICL_SITEPRESS_VERSION' ) && ICL_SITEPRESS_VERSION ) :
	/**
	 * Tell WooCommerce Multilingual that we are caching.
	 * This will add a URL param when switching currency to get the correct response.
	 */
	add_filter( 'wcml_is_cache_enabled_for_switching_currency', '__return_true' );
endif;
