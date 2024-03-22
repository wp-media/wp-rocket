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
		$home_url = apply_filters( 'rocket_saas_is_home_url', home_url(), $url );
		return untrailingslashit( $url ) === untrailingslashit( $home_url );
	}
}
