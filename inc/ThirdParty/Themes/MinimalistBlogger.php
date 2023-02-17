<?php

namespace WP_Rocket\ThirdParty\Themes;

class MinimalistBlogger extends ThirdpartyTheme {
	/**
	 * Theme name
	 *
	 * @var string
	 */
	protected static $theme_name = 'minimalistblogger';

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! self::is_current_theme() ) {
			return [];
		}

		return [
			'rocket_delay_js_exclusions' => 'exclude_jquery_from_delay_js',
		];
	}

	/**
	 * Excludes some MinimalistBlogger JS from delay JS execution
	 *
	 * @since 3.11.3
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
