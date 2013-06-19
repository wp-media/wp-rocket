<?php


/**
 * If uninstall not called from WordPress exit
 *
 * since 1.0
 *
 */

if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

/**
 * Delete option from option table
 *
 * since 1.0
 *
 */

delete_option( 'wp_rocket_settings' );