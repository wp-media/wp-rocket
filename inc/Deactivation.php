<?php

namespace WP_Rocket;

use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Engine\Capabilities\Manager;

class Deactivation {
	/**
	 * Performs these actions during the plugin deactivation
	 *
	 * @return void
	 */
	public static function deactivate() {
		global $is_apache;

        $filesystem = rocket_direct_filesystem();
        $wp_cache   = new WPCache( $filesystem );

        if ( ! isset( $_GET['rocket_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['rocket_nonce'] ), 'force_deactivation' ) ) {
            $causes = [];

            // .htaccess problem.
            if ( $is_apache && ! $filesystem->is_writable( get_home_path() . '.htaccess' ) ) {
                $causes[] = 'htaccess';
            }

            // wp-config problem.
            if (
                ! $wp_cache->find_wpconfig_path()
                &&
                // This filter is documented in inc/Engine/Cache/WPCache.php.
                (bool) apply_filters( 'rocket_set_wp_cache_constant', true )
            ) {
                $causes[] = 'wpconfig';
            }

            if ( count( $causes ) ) {
                set_transient( get_current_user_id() . '_donotdeactivaterocket', $causes );
                wp_safe_redirect( wp_get_referer() );
                die();
            }
        }

        // Delete config files.
        rocket_delete_config_file();

        if ( ! count( glob( WP_ROCKET_CONFIG_PATH . '*.php' ) ) ) {
            // Delete All WP Rocket rules of the .htaccess file.
            flush_rocket_htaccess( true );

            $wp_cache->deactivate();

            // Delete content of advanced-cache.php.
            rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', '' );
        }

        // Update customer key & licence.
        wp_remote_get(
            WP_ROCKET_WEB_API . 'pause-licence.php',
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

        ( new Manager() )->deactivate();

        /**
         * WP Rocket deactivation.
         *
         * @since  3.1.5
         * @author Grégory Viguier
         */
        do_action( 'rocket_deactivation' );
	}
}
