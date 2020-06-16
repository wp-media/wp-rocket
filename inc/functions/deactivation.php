<?php
/**
 * The Rocket Deactivation function.
 *
 * @author  Caspar Green
 * @since   ver 3.6.1
 */

use WP_Rocket\Subscriber\Plugin\Capabilities_Subscriber;

/**
 * Tell WP what to do when plugin is deactivated.
 *
 * @since 1.0
 */
function rocket_deactivation() {
	if ( ! isset( $_GET['rocket_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['rocket_nonce'] ),
			'force_deactivation' ) ) {
		global $is_apache;
		$causes = [];

		// .htaccess problem.
		if ( $is_apache && ! rocket_direct_filesystem()->is_writable( get_home_path() . '.htaccess' ) ) {
			$causes[] = 'htaccess';
		}

		// wp-config problem.
		if ( ! rocket_direct_filesystem()->is_writable( rocket_find_wpconfig_path() ) ) {
			$causes[] = 'wpconfig';
		}

		if ( count( $causes ) ) {
			set_transient( $GLOBALS['current_user']->ID . '_donotdeactivaterocket', $causes );
			wp_safe_redirect( wp_get_referer() );
			die();
		}
	}

	// Delete config files.
	rocket_delete_config_file();

	$config_dir   = new DirectoryIterator( rocket_get_constant( 'WP_ROCKET_CONFIG_PATH' ) );
	$config_files = [];

	foreach ( $config_dir as $file ) {
		if ( $file->isFile() && 'php' === $file->getExtension() ) {
			$config_files[] = $file;
		}
	}

	if ( ! count( $config_files ) ) {
		// Delete All WP Rocket rules of the .htaccess file.
		flush_rocket_htaccess( true );

		// Remove WP_CACHE constant in wp-config.php.
		set_rocket_wp_cache_define( false );

		// Delete content of advanced-cache.php.
		rocket_put_content( rocket_get_constant( 'WP_CONTENT_DIR' ) . '/advanced-cache.php', '' );
	}

	// Update customer key & licence.
	wp_remote_get(
		rocket_get_constant( 'WP_ROCKET_WEB_API' ) . 'pause-licence.php',
		[
			'blocking' => false,
		]
	);

	// Delete transients.
	delete_transient( 'rocket_check_licence_30' );
	delete_transient( 'rocket_check_licence_1' );
	delete_site_transient( 'update_wprocket_response' );

	// Unschedule WP Cron events.
	wp_clear_scheduled_hook( 'rocket_facebook_tracking_cache_update' );
	wp_clear_scheduled_hook( 'rocket_google_tracking_cache_update' );
	wp_clear_scheduled_hook( 'rocket_cache_dir_size_check' );

	/**
	 * WP Rocket deactivation.
	 *
	 * @since  3.1.5
	 * @author Grégory Viguier
	 */
	do_action( 'rocket_deactivation' );

	( new Capabilities_Subscriber() )->remove_rocket_capabilities();
}
