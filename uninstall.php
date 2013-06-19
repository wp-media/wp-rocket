<?php

// If uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

/**
 * Delete option and transient from option table
 *
 * since 1.0
 *
 */

delete_site_transient( 'update_rocket' );
delete_option( 'wp_rocket_settings' );