<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

class ThemeResolver {
	/**
	 * Array of themes names with compatibility classes
	 *
	 * @var array
	 */
	private static $compatibilities = [
		'avada',
		'bridge',
		'divi',
		'flatsome',
		'jevelin',
		'minimalist_blogger',
		'polygon',
		'uncode',
		'xstore',
		'themify',
		'shoptimizer',
	];

	/**
	 * Return name of current theme
	 *
	 * @return string
	 */
	public static function get_current_theme(): string {
		$theme    = wp_get_theme();
		$template = $theme->get_template();

		if ( empty( $template ) ) {
			return '';
		}

		$template = strtolower( $template );

		if ( ! in_array( $template, self::$compatibilities, true ) ) {
			return '';
		}

		return $template;
	}
}
