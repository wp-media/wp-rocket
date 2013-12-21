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



/**
 * Remove all cache files
 *
 * @since 1.2.0
 *
 */
function __rocket_rrmdir( $dir )
{

	if( !is_dir( $dir ) ):
		@unlink( $dir );
		return;
	endif;
	
    if( $globs = glob( $dir . '/*' ) ) {
	    
	    foreach( $globs as $file )
	        is_dir( $file ) ? __rocket_rrmdir($file) : @unlink( $file );	
	}

    @rmdir($dir);
    
}

__rocket_rrmdir( WP_CONTENT_DIR . '/cache/wp-rocket/' );