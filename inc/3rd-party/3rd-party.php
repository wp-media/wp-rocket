<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

require( WP_ROCKET_3RD_PARTY_PATH . 'wpengine.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'godaddy.php' );

if ( version_compare( phpversion(), '5.3.0', '>=' ) ) {
	require( WP_ROCKET_3RD_PARTY_PATH . 'savvii.php' );
}

require( WP_ROCKET_3RD_PARTY_PATH . 'kk-star-ratings.php' );
require( WP_ROCKET_3RD_PARTY_PATH . 'wp-postratings.php' );