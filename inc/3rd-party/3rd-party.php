<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

require( WP_ROCKET_3RD_PARTY_PATH . 'hosting/wpengine.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'hosting/godaddy.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'hosting/flywheel.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'hosting/wp-serveur.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'hosting/varnish.php' );

if ( version_compare( phpversion(), '5.3.0', '>=' ) ) {
	require( WP_ROCKET_3RD_PARTY_PATH . 'hosting/savvii.php' );
}

require( WP_ROCKET_3RD_PARTY_PATH . 'slider/revslider.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'i18n/wpml.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'i18n/polylang.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/woocommerce.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/aelia-currencyswitcher.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/woocommerce-currency-converter-widget.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/edd-software-licencing.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'age-verify.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'autoptimize.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'eu-cookie-law.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'kk-star-ratings.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'wp-postratings.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'wp-print.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'buddypress.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'give.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'custom-login.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'mobile/amp.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'jetpack.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'yoast-seo.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'all-in-one-seo-pack.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'wp-rest-api.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'page-builder/beaver-builder.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'page-builder/thrive-visual-editor.php' );
