<?php

namespace WP_Rocket\ThirdParty\Themes;

use WP_Theme;
use WP_Rocket\Event_Management\Subscriber_Interface;
abstract class ThirdpartyTheme implements Subscriber_Interface {
	/**
	 * Name from the theme.
	 *
	 * @var string
	 */
	protected static $theme_name = '';
	/**
	 * Check if the theme or one of its child theme is activated.
	 *
	 * @param WP_Theme $theme current theme.
	 * @return bool
	 */
	protected static function is_current_theme( $theme = null ) {
		$theme = $theme instanceof WP_Theme ? $theme : wp_get_theme();

		return ( str_contains( static::$theme_name, $theme->get_template() ) );
	}
}
