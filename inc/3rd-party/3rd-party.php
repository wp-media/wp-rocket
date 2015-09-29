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
require( WP_ROCKET_3RD_PARTY_PATH . 'kk-star-ratings.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'wp-postratings.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'wp-print.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'buddypress.php' );