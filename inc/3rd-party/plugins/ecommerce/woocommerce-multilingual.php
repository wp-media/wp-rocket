<?php

defined( 'ABSPATH' ) || exit;

// Compatibility with the currency switcher in WooCommerce Multilingual plugin.
if ( defined( 'WCML_VERSION' ) ) :
	add_action( 'wcml_switch_currency', 'rocket_clean_domain' );
endif;
