<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

class Flatsome extends ThirdpartyTheme {
	/**
	 * Theme name
	 *
	 * @var string
	 */
	protected static $theme_name = 'flatsome';

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! self::is_current_theme() ) {
			return [];
		}

		return [ 'rocket_rucss_inline_content_exclusions' => 'preserve_patterns' ];
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

		$preserve = [
			'#section_',
			'#text-box-',
			'#banner-',
			'#slider-',
			'#gap-',
			'#image_',
			'#row-',
			'#text-',
			'#banner-grid-',
			'#cats-',
			'#col-',
			'#gallery-',
			'#instagram-',
			'#map-',
			'#page-header-',
			'#pages-',
			'#panel-',
			'#portfolio-',
			'#product-flip-',
			'#product-grid-',
			'#stack-',
			'#timer-',
			'#title-',
		];

		return array_merge( $patterns, $preserve );
	}
}
