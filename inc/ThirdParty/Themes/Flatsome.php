<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Flatsome implements Subscriber_Interface {
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
	 * @since 3.11
	 *
	 * @param array $patterns Array of patterns to preserve.
	 *
	 * @return array
	 */
	public function preserve_patterns( $patterns ): array {
		if ( ! self::is_flatsome() ) {
			return $patterns;
		}

		$preserve = [
			'#section_',
			'#text-box-',
			'#banner-',
			'#slider-',
			'#gap-',
			'#image_',
			'#row-',
			'#text-',
		];

		return array_merge( $patterns, $preserve );
	}

	/**
	 * Checks if the current theme (or parent) is Flatsome
	 *
	 * @since 3.11
	 *
	 * @param WP_Theme $theme Instance of the theme.
	 */
	private static function is_flatsome( $theme = null ) {
		$theme = $theme instanceof \WP_Theme ? $theme : wp_get_theme();

		return 'flatsome' === strtolower( $theme->get( 'Name' ) ) || 'flatsome' === strtolower( $theme->get_template() );
	}
}
