<?php

namespace WP_Rocket\ThirdParty\Hostings;

use PagelyCachePurge;

class Pagely extends AbstractNoCacheHost
{

	public static function get_subscribed_events()
	{
		return [
			'after_rocket_clean_domain' => 'clean_pagely'
		];
	}

	/**
	 * Call the cache server to purge the cache with Pagely hosting.
	 *
	 * @since 2.5.7
	 *
	 * @return void
	 */
	function clean_pagely() {
		if ( class_exists( 'PagelyCachePurge' ) ) {
			$purger = new PagelyCachePurge();
			$purger->purgeAll();
		}
	}
}
