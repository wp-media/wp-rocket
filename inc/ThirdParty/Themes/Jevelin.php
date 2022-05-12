<?php
namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Jevelin implements Subscriber_Interface {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_rucss_inline_content_exclusions' => 'preserve_patterns',
		];
	}

	/**
	 * Preserves the CSS patterns when adding the used CSS to the page
	 *
	 * @since 3.11.3
	 *
	 * @param array $patterns Array of patterns to preserve.
	 *
	 * @return array
	 */
	public function preserve_patterns( $patterns ): array {
		if ( ! self::is_jevelin() ) {
			return $patterns;
		}

		$patterns[] = '#heading-';

		return $patterns;
	}

	/**
	 * Checks if the current theme (or parent) is Jevelin
	 *
	 * @since 3.11.3
	 *
	 * @param WP_Theme $theme Instance of the theme.
	 */
	private static function is_jevelin( $theme = null ) {
		$theme = $theme instanceof \WP_Theme ? $theme : wp_get_theme();

		return 'jevelin' === strtolower( $theme->get( 'Name' ) ) || 'jevelin' === strtolower( $theme->get_template() );
	}
}
