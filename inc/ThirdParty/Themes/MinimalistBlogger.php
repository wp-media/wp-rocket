<?php

namespace WP_Rocket\ThirdParty\Themes;

class MinimalistBlogger extends ThirdpartyTheme {

	/**
	 * Name from the theme.
	 *
	 * @var string
	 */
	protected static $theme_name = 'minimalist-blogger';

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [];
		if ( self::is_theme() ) {
			$events['rocket_delay_js_exclusions'] = 'exclude_jquery_from_delay_js';
		}
		return $events;
	}

	/**
	 * Excludes some MinimalistBlogger JS from delay JS execution
	 *
	 * @since 3.11.2
	 *
	 * @param array $exclusions Array of exclusion patterns.
	 *
	 * @return array
	 */
	public function exclude_jquery_from_delay_js( array $exclusions = [] ) {
		$exclusions[] = '/jquery-?[0-9.](.*)(.min|.slim|.slim.min)?.js';
		$exclusions[] = '/jquery-migrate(.min)?.js';
		return $exclusions;
	}
}
