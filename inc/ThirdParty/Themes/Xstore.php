<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

class Xstore extends ThirdpartyTheme {
	/**
	 * Theme name
	 *
	 * @var string
	 */
	protected static $theme_name = 'xstore';

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {

		if ( ! self::is_current_theme() ) {
			return [];
		}

		return [ 'rocket_rucss_inline_content_exclusions' => 'exclude_inline_content' ];
	}

	/**
	 * Excludes the contents with patterns from being processed by RUCSS
	 *
	 * @param array $patterns Array of patterns to preserve.
	 *
	 * @return array
	 */
	public function exclude_inline_content( $patterns ): array {

		$patterns[] = '.slider-';
		$patterns[] = '.slider-item-';

		return $patterns;
	}
}
