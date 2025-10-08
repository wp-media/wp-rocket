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
			$params['wp_http_referer'] = rawurlencode( remove_query_arg( 'fl_builder', $referer_url ) . self::get_filtered_tab_hash( $action ) );
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
	 * Retrieves the filtered tab hash for the admin interface based on the current action.
	 *
	 * This method applies the 'rocket_http_referer_tab_hash' filter to allow customization
	 * of the tab hash value used in admin URLs. It ensures that the returned tab hash is
	 * within the list of allowed tab hashes. If the filtered tab hash is not allowed or is empty,
	 * an empty string is returned. Otherwise, the tab hash is returned prefixed with '#'.
	 *
	 * @since 3.20.1
	 *
	 * @param string $action The current action being performed.
	 *
	 * @return string The filtered and validated tab hash value, prefixed with '#', or an empty string if not valid.
	 */
	private static function get_filtered_tab_hash( string $action ): string {
		$allowed_tab_hashes = [
			'dashboard',
			'rocket_insights',
			'file_optimization',
			'media',
			'preload',
			'advanced_cache',
			'database',
			'page_cdn',
			'heartbeat',
			'addons',
			'imagify',
			'tools',
			'tutorials',
			'plugins',
		];

		/**
		 * Filters the tab hash for the admin interface.
		 *
		 * This filter allows customization of the tab hash value used in admin URLs.
		 *
		 * @param string $tab_hash The current tab hash value (default: empty string).
		 * @param string $action   The current action being performed.
		 *
		 * @return string The filtered tab hash value.
		 */
		$tab_hash = wpm_apply_filters_typed( 'string', 'rocket_http_referer_tab_hash', '', $action );

		// Return early for default value.
		if ( '' === $tab_hash ) {
			return '';
		}

		// Return early if not valid tab hash.
		if ( ! in_array( $tab_hash, $allowed_tab_hashes, true ) ) {
			return '';
		}

		return '#' . $tab_hash;
	}
}
