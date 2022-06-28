<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Xstore implements Subscriber_Interface {
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

	/**
	 * Checks if the current theme is Xstore
	 *
	 * @param WP_Theme $theme Instance of the theme.
	 */
	private static function is_current_theme( $theme = null ) {
		$theme = $theme instanceof \WP_Theme ? $theme : wp_get_theme();

		return 'xstore' === strtolower( $theme->get( 'Name' ) ) || 'xstore' === strtolower( $theme->get_template() );
	}
}
