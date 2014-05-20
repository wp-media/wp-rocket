<?php

// If uninstall not called from WordPress exit
defined( 'WP_UNINSTALL_PLUGIN' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Delete option and transient from option table
 *
 * since 1.0
 *
 */

delete_site_transient( 'update_wprocket' );
delete_option( 'wp_rocket_settings' );
delete_metadata( 'user', '', 'rocket_boxes', '', true ); // magical true


/**
 * Remove all cache files
 *
 * @since 1.2.0
 *
 */
function __rocket_rrmdir( $dir )
{

	if( !is_dir( $dir ) ) 
	{
		@unlink( $dir );
		return;	
	}

    if( $globs = glob( $dir . '/*', GLOB_NOSORT ) ) {
	    foreach( $globs as $file ) {
			is_dir( $file ) ? __rocket_rrmdir($file) : @unlink( $file );
	    }
	}

    @rmdir($dir);

}

__rocket_rrmdir( WP_CONTENT_DIR . '/cache/wp-rocket/' );
__rocket_rrmdir( WP_CONTENT_DIR . '/cache/min/' );
__rocket_rrmdir( WP_CONTENT_DIR . '/wp-rocket-config/' );