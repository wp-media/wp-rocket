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
}
