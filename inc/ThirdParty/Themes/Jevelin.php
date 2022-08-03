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
		if ( ! self::is_jevelin() ) {
			return [];
		}
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

		return array_merge(
			$patterns,
			[
				'#heading-',
				'#button-',
				'#image-points-',
				'#image-comparison-',
				'#partners-',
				'#icon-',
				'#text-block-',
				'#instagram-feed-',
				'#woocommerce-products-',
				'#tabs-',
				'#icon-group-',
				'#alert-',
				'#divider-',
				'#image-gallery-',
				'#portfolio-fancy-',
				'#counter-',
				'#text-group-',
				'#piechart-',
				'#single-image-',
				'#progress-',
				'#countdown-',
				'#testimonials-',
				'#portfolio-',
				'#event-',
				'.vc_column_',
				'.vc_row_',
				'.sh-empty-space-',
				'.sh-element-titlebar-title-',
				'.sh-footer-builder-widgets-',
				'.sh-image-gallery-simple-',
				'.header-navigation-',
				'.sh-google-maps-',
				'.sh-element-titlebar-breadcrumbs-',
				'.sh-header-builder-',
				'.sh-text-group-',
				'.sh-footer-builder-title-',
			]
			);
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
