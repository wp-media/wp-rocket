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

		$patterns[] = '#heading-';
		$patterns[] = '#button-';
		$patterns[] = '#image-points-';
		$patterns[] = '#image-comparison-';
		$patterns[] = '#partners-';
		$patterns[] = '#icon-';
		$patterns[] = '#text-block-';
		$patterns[] = '#instagram-feed-';
		$patterns[] = '#woocommerce-products-';
		$patterns[] = '#tabs-';
		$patterns[] = '#icon-group-';
		$patterns[] = '#alert-';
		$patterns[] = '#divider-';
		$patterns[] = '#image-gallery-';
		$patterns[] = '#portfolio-fancy-';
		$patterns[] = '#counter-';
		$patterns[] = '#text-group-';
		$patterns[] = '#piechart-';
		$patterns[] = '#single-image-';
		$patterns[] = '#progress-';
		$patterns[] = '#countdown-';
		$patterns[] = '#testimonials-';
		$patterns[] = '#portfolio-';
		$patterns[] = '#event-';
		$patterns[] = '.vc_column_';
		$patterns[] = '.vc_row_';
		$patterns[] = '.sh-empty-space-';
		$patterns[] = '.sh-element-titlebar-title-';
		$patterns[] = '.sh-footer-builder-widgets-';
		$patterns[] = '.sh-image-gallery-simple-';
		$patterns[] = '.header-navigation-';
		$patterns[] = '.sh-google-maps-';
		$patterns[] = '.sh-element-titlebar-breadcrumbs-';
		$patterns[] = '.sh-header-builder-';
		$patterns[] = '.sh-text-group-';
		$patterns[] = '.sh-footer-builder-title-';

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
