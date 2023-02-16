<?php

namespace WP_Rocket\ThirdParty\Hostings;

class Ngnix extends AbstractNoCacheHost
{

	public static function get_subscribed_events()
	{
		return [
			'rocket_cache_query_strings' => 'better_nginx_compatibility'
		];
	}

	/**
	 * Compatibility with an usual NGINX configuration which include:
	 *      try_files $uri $uri/ /index.php?q=$uri&$args
	 *
	 * @since 2.3.9
	 *
	 * @param array $query_strings Array of query strings to cache.
	 *
	 * @return array Updated array of query strings.
	 */
	function better_nginx_compatibility( $query_strings ) {
		global $is_nginx;

		if ( $is_nginx ) {
			$query_strings[] = 'q';
		}

		return $query_strings;
	}
}
