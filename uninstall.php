<?php

// If uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();


// Delete option from option table
delete_option( 'wp_rocket_settings' );	