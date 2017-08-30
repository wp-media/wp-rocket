<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

// Compatibility with the currency switcher in WooCommerce Multilingual plugin.
if ( defined( 'WCML_VERSION' ) ) :
	add_action( 'wcml_switch_currency', 'rocket_clean_domain' );
endif;
