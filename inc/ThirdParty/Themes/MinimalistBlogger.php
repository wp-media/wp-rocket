<?php

namespace WP_Rocket\ThirdParty\Themes;

class MinimalistBlogger implements \WP_Rocket\Event_Management\Subscriber_Interface
{

    /**
     * @inheritDoc
     */
    public static function get_subscribed_events()
    {
        $events = [];
		if(self::is_mb()) {
			$events['rocket_delay_js_exclusions'] = 'exclude_jquery_from_delay_js';
		}
		return $events;
    }

	/**
	 * Excludes some MinimalistBlogger JS from delay JS execution
	 *
	 * @since 3.10.2
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

	/**
	 * Checks if the current theme (or parent) is Divi
	 *
	 * @since 3.6.3
	 *
	 * @param WP_Theme $theme Instance of the theme.
	 */
	private static function is_mb( $theme = null ) {
		$theme = $theme instanceof WP_Theme ? $theme : wp_get_theme();

		return ( str_contains('minimalist-blogger', $theme->get_template()));
	}
}
