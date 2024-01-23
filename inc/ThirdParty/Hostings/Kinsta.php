<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Kinsta implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Subscribed events for Kinsta.
	 *
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		global $kinsta_cache;

		$events = [
			'do_rocket_generate_caching_files'   => [ 'return_false', PHP_INT_MAX ],
			'rocket_display_varnish_options_tab' => 'return_false',
			'rocket_cache_mandatory_cookies'     => [ 'return_empty_array', PHP_INT_MAX ],
		];

		if ( isset( $kinsta_cache ) ) {
			$events['rocket_after_clean_domain']           = 'clean_kinsta_cache';
			$events['after_rocket_clean_post']             = 'clean_kinsta_post_cache';
			$events['rocket_rucss_after_clearing_usedcss'] = 'clean_kinsta_cache_url';
			$events['rocket_rucss_complete_job_status']    = 'clean_kinsta_cache_url';
			$events['after_rocket_clean_home']             = [ 'clean_kinsta_cache_home', 10, 2 ];
			$events['after_rocket_clean_file']             = 'clean_kinsta_cache_url';
			$events['wp_rocket_loaded']                    = 'remove_partial_purge_hooks';
			return $events;
		}

		$events['admin_notices'] = 'display_error_notice';
		return $events;
	}

	/**
	 * Clear Kinsta cache when clearing WP Rocket cache
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function clean_kinsta_cache() {
		global $kinsta_cache;

		if ( ! empty( $kinsta_cache->kinsta_cache_purge ) ) {
			$kinsta_cache->kinsta_cache_purge->purge_complete_caches();
		}
	}

	/**
	 * Partially clear Kinsta cache when partially clearing WP Rocket cache
	 *
	 * @since 3.0
	 *
	 * @param object $post Post object.
	 * @return void
	 */
	public function clean_kinsta_post_cache( $post ) {
		global $kinsta_cache;
		$kinsta_cache->kinsta_cache_purge->initiate_purge( $post->ID, 'post' );
	}

	/**
	 * Clears Kinsta cache for the homepage URL when using "Purge this URL" from the admin bar on the front end
	 *
	 * @since 3.0.4
	 *
	 * @param string $root WP Rocket root cache path.
	 * @param string $lang Current language.
	 * @return void
	 */
	public function clean_kinsta_cache_home( $root = '', $lang = '' ) {
		$url = get_rocket_i18n_home_url( $lang );
		$url = trailingslashit( $url ) . 'kinsta-clear-cache/';

		wp_safe_remote_get(
			$url,
			[
				'blocking' => false,
				'timeout'  => 0.01,
			]
		);
	}

	/**
	 * Clears Kinsta cache for a specific URL when using "Purge this URL" from the admin bar on the front end
	 *
	 * @since 3.0.4
	 *
	 * @param string $url URL to purge.
	 * @return void
	 */
	public function clean_kinsta_cache_url( $url ) {
		$url = trailingslashit( $url ) . 'kinsta-clear-cache/';

		wp_safe_remote_get(
			$url,
			[
				'blocking' => false,
				'timeout'  => 0.01,
			]
		);
	}

	/**
	 * Remove WP Rocket functions on WP core action hooks to prevent triggering a double cache clear.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function remove_partial_purge_hooks() {
		// WP core action hooks clean_post() gets hooked into.
		$clean_post_hooks = [
			// Disables the refreshing of partial cache when content is edited.
			'wp_trash_post',
			'delete_post',
			'clean_post_cache',
			'wp_update_comment_count',
		];

		// Remove rocket_clean_post() from core action hooks.
		array_map(
			function ( $hook ) {
				remove_action( $hook, 'rocket_clean_post' );
			},
			$clean_post_hooks
		);

		remove_filter( 'rocket_clean_files', 'rocket_clean_files_users' );
	}

	/**
	 * Display notice when we are on Kinsta but the plugin is not present.
	 *
	 * @return void
	 */
	public function display_error_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				// translators: %1$s = opening link tag, %2$s = closing link tag.
				'message'     => sprintf( __( 'Your installation seems to be missing core Kinsta files managing Cache clearing, which will prevent your Kinsta installation and WP Rocket from working correctly. Please get in touch with Kinsta support through your %1$sMyKinsta%2$s account to resolve this issue.', 'rocket' ), '<a href="https://my.kinsta.com/login/" target="_blank">', '</a>' ),
			]
		);
	}
}
