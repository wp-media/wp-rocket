<?php

namespace WP_Rocket\ThirdParty\Themes;

use WP_Theme;

abstract class ThirdpartyTheme implements \WP_Rocket\Event_Management\Subscriber_Interface {

	/**
	 * Current theme.
	 *
	 * @var WP_Theme
	 */
	protected static $theme;
	/**
	 * Name from the theme.
	 *
	 * @var string
	 */
	protected static $theme_name = '';
	/**
	 * Check if the theme or one of its child theme is activated.
	 *
	 * @return bool
	 */
	protected static function is_theme() {
		$theme = self::$theme instanceof WP_Theme ? self::$theme : wp_get_theme();

		return ( str_contains( static::$theme_name, $theme->get_template() ) );
	}
}
