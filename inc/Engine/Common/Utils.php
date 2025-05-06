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
}
