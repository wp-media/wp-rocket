<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( isset( $_SERVER['KINSTA_CACHE_ZONE'] ) ) {

	add_filter( 'do_rocket_generate_caching_files', '__return_false', PHP_INT_MAX );
	add_filter( 'rocket_display_varnish_options_tab', '__return_false' );
	// Prevent mandatory cookies on hosting with server cache.
	add_filter( 'rocket_cache_mandatory_cookies', '__return_empty_array', PHP_INT_MAX );

	global $kinsta_cache;

	if ( isset( $kinsta_cache ) && class_exists( '\\Kinsta\\CDN_Enabler' ) ) {
		/**
		 * Clear Kinsta cache when clearing WP Rocket cache
		 *
		 * @since 3.0
		 * @author Remy Perona
		 *
		 * @return void
		 */
		function rocket_clean_kinsta_cache() {
			global $kinsta_cache;

			if ( ! empty( $kinsta_cache->kinsta_cache_purge ) ) {
				$kinsta_cache->kinsta_cache_purge->purge_complete_caches();
			}
		}
		add_action( 'after_rocket_clean_domain', 'rocket_clean_kinsta_cache' );

		/**
		 * Partially clear Kinsta cache when partially clearing WP Rocket cache
		 *
		 * @since 3.0
		 * @author Remy Perona
		 *
		 * @param object $post Post object.
		 * @return void
		 */
		function rocket_clean_kinsta_post_cache( $post ) {
			global $kinsta_cache;
			$kinsta_cache->kinsta_cache_purge->initiate_purge( $post->ID, 'post' );
		}
		add_action( 'after_rocket_clean_post', 'rocket_clean_kinsta_post_cache' );

		/**
		 * Clears Kinsta cache for the homepage URL when using "Purge this URL" from the admin bar on the front end
		 *
		 * @since 3.0.4
		 * @author Remy Perona
		 *
		 * @param string $root WP Rocket root cache path.
		 * @param string $lang Current language.
		 * @return void
		 */
		function rocket_clean_kinsta_cache_home( $root = '', $lang = '' ) {
			$url = get_rocket_i18n_home_url( $lang );
			$url = trailingslashit( $url ) . 'kinsta-clear-cache/';

			wp_remote_get( $url, array(
				'blocking' => false,
				'timeout'  => 0.01,
			) );
		}
		add_action( 'after_rocket_clean_home', 'rocket_clean_kinsta_cache_home', 10, 2 );

		/**
		 * Clears Kinsta cache for a specific URL when using "Purge this URL" from the admin bar on the front end
		 *
		 * @since 3.0.4
		 * @author Remy Perona
		 *
		 * @param string $url URL to purge.
		 * @return void
		 */
		function rocket_clean_kinsta_cache_url( $url ) {
			$url = trailingslashit( $url ) . 'kinsta-clear-cache/';

			wp_remote_get( $url, array(
				'blocking' => false,
				'timeout'  => 0.01,
			) );
		}
		add_action( 'after_rocket_clean_file', 'rocket_clean_kinsta_cache_url' );

		/**
		 * Remove WP Rocket functions on WP core action hooks to prevent triggering a double cache clear.
		 *
		 * @since 3.0
		 * @author Remy Perona
		 *
		 * @return void
		 */
		function rocket_remove_partial_purge_hooks() {
			// WP core action hooks rocket_clean_post() gets hooked into.
			$clean_post_hooks = array(
				// Disables the refreshing of partial cache when content is edited.
				'wp_trash_post',
				'delete_post',
				'clean_post_cache',
				'wp_update_comment_count',
			);

			// Remove rocket_clean_post() from core action hooks.
			array_map(
				function( $hook ) {
					remove_action( $hook, 'rocket_clean_post' );
				},
				$clean_post_hooks
			);

			remove_filter( 'rocket_clean_files', 'rocket_clean_files_users' );
		}
		add_action( 'wp_rocket_loaded', 'rocket_remove_partial_purge_hooks' );

		if ( \Kinsta\CDN_Enabler::cdn_is_enabled() ) {
			/**
			 * Add Kinsta CDN to WP Rocket CDN hosts list if enabled
			 *
			 * @since 3.0
			 * @author Remy Perona
			 *
			 * @param Array $hosts Array of CDN hosts.
			 * @return Array Updated array of CDN hosts
			 */
			function rocket_add_kinsta_cdn_cname( $hosts ) {
				$hosts[] = $_SERVER['KINSTA_CDN_DOMAIN'];

				return $hosts;
			}
			add_filter( 'rocket_cdn_cnames', 'rocket_add_kinsta_cdn_cname', 1 );
		}
	} else {
		add_action( 'admin_notices', function() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$screen = get_current_screen();

			if ( 'settings_page_wprocket' !== $screen->id ) {
				return;
			}

			rocket_notice_html(
				array(
					'status'      => 'error',
					'dismissible' => '',
					// translators: %1$s = opening link tag, %2$s = closing link tag.
					'message'     => sprintf( __( 'Your installation seems to be missing core Kinsta files managing Cache clearing and CDN, which will prevent your Kinsta installation and WP Rocket from working correctly. Please get in touch with Kinsta support through your %1$sMyKinsta%2$s account to resolve this issue.', 'rocket' ), '<a href="https://my.kinsta.com/login/" target="_blank">', '</a>' ),
				)
			);
		} );
	}
}
