<?php

namespace WP_Rocket\Engine\Common;

class Utils {

	/**
	 * Check if current page is the home page.
	 *
	 * @param string $url Current page url.
	 *
	 * @return bool
	 */
	public static function is_home( string $url ): bool {
		/**
		 * Filters the home url.
		 *
		 * @since 3.11.4
		 *
		 * @param string  $home_url home url.
		 * @param string  $url url of current page.
		 */
		$home_url = rocket_apply_filter_and_deprecated(
			'rocket_saas_is_home_url',
			[ home_url(), $url ],
			'3.16',
			'rocket_rucss_is_home_url'
		);
		return untrailingslashit( $url ) === untrailingslashit( $home_url );
	}

	/**
	 * Checks if current request is coming from our SaaS.
	 *
	 * @return bool
	 */
	public static function is_saas_visit(): bool {
		return isset( $_SERVER['HTTP_WPR_OPT_LIST'] );
	}

	/**
	 * Checks if current request is coming from our inspector tool.
	 *
	 * @return bool
	 */
	public static function is_inspector_visit(): bool {
		return isset( $_GET['wpr_lazyrendercontent'] );// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get admin post nonce url.
	 *
	 * @param string $action Action.
	 * @param array  $params Additional Parameters.
	 *
	 * @return string
	 */
	public static function get_nonce_post_url( string $action, array $params = [] ): string {
		$params['action'] = $action;

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url               = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );
			$params['wp_http_referer'] = rawurlencode( remove_query_arg( 'fl_builder', $referer_url ) );
		}

		return wp_nonce_url(
			add_query_arg(
				$params,
				admin_url( 'admin-post.php' )
			),
			$action
		);
	}

	/**
	 * Processes deleted cache file paths and return an array of processed urls.
	 *
	 * Iterates through deleted cache file metadata, converts file paths to URLs, and passes each URL to an optional callback.
	 *
	 * @param array         $deleted  An array of deleted file data arrays. Each array should include:
	 *                                - 'home_url'   (string): The site's home URL.
	 *                                - 'home_path'  (string): The site's home path.
	 *                                - 'logged_in'  (bool): Whether the user was logged in.
	 *                                - 'files'      (array): List of file paths that have been deleted.
	 * @param callable|null $callback Optional. Callback function to execute for each URL. Receives the URL (string) as the only argument.
	 *
	 * @return array Array of URLs that were processed.
	 */
	public static function process_deleted_cache_urls( array $deleted, ?callable $callback = null ): array {
		// Initialize an array to store the processed URLs.
		$urls = [];

		foreach ( $deleted as $data ) {
			if ( $data['logged_in'] ) {
				// Logged in user: no need to preload those since we would need the corresponding cookies.
				continue;
			}
			foreach ( $data['files'] as $file_path ) {
				if ( false !== strpos( $file_path, '#' ) ) {
					// URL with query string.
					$file_path = preg_replace( '/#/', '?', $file_path, 1 );
				} else {
					$file_path         = untrailingslashit( $file_path );
					$data['home_path'] = untrailingslashit( $data['home_path'] );
					$data['home_url']  = untrailingslashit( $data['home_url'] );
					if ( '/' === substr( get_option( 'permalink_structure' ), -1 ) ) {
						$file_path         .= '/';
						$data['home_path'] .= '/';
						$data['home_url']  .= '/';
					}
				}

				// Convert file path to URL.
				$url = str_replace( $data['home_path'], $data['home_url'], $file_path );

				// Add the processed URL to the array that will be returned.
				$urls[] = $url;

				// If callback provided, execute it; otherwise collect URLs.
				if ( null !== $callback ) {
					call_user_func( $callback, $url );
				}
			}
		}

		return $urls;
	}

	/**
	 * Display an update notice when the plugin is updated.
	 *
	 * @param array $notice_info Notice information {.
	 * @type string $new version New Version of the plugin.
	 * @type string $previous_version Previous version of the plugin.
	 * @type string $message Notice message.
	 * @type string $action Notice action.
	 * @type string $dismiss_message Dismiss message button title.
	 * @type string $dismiss_button Dismiss button.
	 *  }
	 *
	 * @param bool  $display_general Whether to display the notice on all WP or only WPR dashboard.
	 * @return void
	 */
	public static function display_update_notice( array $notice_info, $display_general = false ): void {
		$previous_version = $notice_info['previous_version'] ?? '';
		$status           = $notice_info['status'] ?? 'info';
		$version          = $notice_info['new_version'] ?? '';

		// If previous_version is set, this is an upgrade — check version compatibility before displaying the notice.
		if ( ! empty( $previous_version ) ) {
			if ( version_compare( $previous_version, $version, '>=' ) ) {
				return;
			}
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( ! $display_general && 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( $notice_info['dismiss_button'], (array) $boxes, true ) ) {
			return;
		}

		if ( $notice_info['track_event'] ?? false ) {
			/**
			 * Fires after the RocketCDN notice is displayed, allowing to track the impression of the notice.
			 *
			 * @param string $dismiss_button The notice button identifier.
			 */
			do_action( 'rocket_notice_displayed', $notice_info['dismiss_button'] );
		}

		$notice_id = 'rocket-notice-' . sanitize_html_class( $notice_info['dismiss_button'] );

		rocket_notice_html(
			[
				'id'                     => $notice_id,
				'status'                 => $status,
				'dismissible'            => 'is-dismissible',
				'message'                => $notice_info['message'],
				'action'                 => $notice_info['action'],
				'dismiss_button'         => $notice_info['dismiss_button'],
				'dismiss_button_message' => $notice_info['dismiss_message'],
				'dismiss_button_class'   => 'button button-secondary',
			]
		);

		$nonce = wp_create_nonce( 'rocket_ignore_' . $notice_info['dismiss_button'] );
		?>
		<script>
			window.addEventListener( 'DOMContentLoaded', function() {
				var notice = document.getElementById( '<?php echo esc_js( $notice_id ); ?>' );
				if ( ! notice ) {
					return;
				}

				notice.addEventListener( 'click', function( event ) {
					var target = event.target;

					if ( ! target.closest( '.notice-dismiss' ) ) {
						return;
					}

					var httpRequest = new XMLHttpRequest();
					httpRequest.open( 'GET', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?action=rocket_ignore&box=<?php echo esc_js( rawurlencode( $notice_info['dismiss_button'] ) ); ?>&_wpnonce=<?php echo esc_js( $nonce ); ?>' );
					httpRequest.send();
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Clean url's cache.
	 *
	 * @param string $url Url to clear cache for.
	 * @return void
	 */
	public static function clean_url( string $url ): void {
		if ( self::is_home( $url ) ) {
			rocket_clean_home();

			return;
		}

		rocket_clean_files( [ $url ] );
	}

	/**
	 * Checks if the given setting's value changed.
	 *
	 * @param string $setting The settings's value to check in the old and new values.
	 * @param mixed  $old_value Old option value.
	 * @param mixed  $value     New option value.
	 *
	 * @return bool
	 */
	public static function did_setting_change( $setting, $old_value, $value ) {
		return (
			isset( $old_value[ $setting ] )
			&&
			isset( $value[ $setting ] )
			&&
			// phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
			$old_value[ $setting ] != $value[ $setting ]
		);
	}
}
