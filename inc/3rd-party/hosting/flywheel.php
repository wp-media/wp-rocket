<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'FlywheelNginxCompat' ) ) :

/**
  * Allow to purge Varnish on Flywheel websites
  *
  * @since 2.6.8
 */
add_filter( 'do_rocket_varnish_http_purge', '__return_true' );

/**
  * Set up the right Varnish IP for Flywheel
  *
  * @since 2.6.8
 */
add_filter( 'rocket_varnish_ip'	, '__rocket_varnish_ip_on_flywheel' );
function __rocket_varnish_ip_on_flywheel() {
	return '127.0.0.1';
}

/**
  * Don't display the Varnish options tab for Flywheel users
  *
  * @since 2.7
 */
add_filter( 'rocket_display_varnish_options_tab', '__return_false' );
	
endif;