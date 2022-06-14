<?php

namespace WP_Rocket\Engine\Preload\Controller;

trait CheckExcludedTrait {

	/**
	 * Check if the url is excluded.
	 *
	 * @param string $url url to check.
	 * @return bool
	 */
	protected function is_excluded( string $url ) {
		$excluded = str_replace( '/', '\/', get_rocket_cache_reject_uri() );
		return (bool) preg_match( "/$excluded/", $url );
	}
}
