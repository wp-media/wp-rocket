<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket_Mobile_Detect;

class Meta {
	/**
	 * Mobile Detect instance
	 *
	 * @var WP_Rocket_Mobile_Detect
	 */
	private $mobile_detect;

	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class
	 *
	 * @param WP_Rocket_Mobile_Detect $mobile_detect Mobile Detect instance.
	 * @param Options_Data            $options Options instance.
	 */
	public function __construct( WP_Rocket_Mobile_Detect $mobile_detect, Options_Data $options ) {
		$this->mobile_detect = $mobile_detect;
		$this->options       = $options;
	}

	/**
	 * Add the WP Rocket meta generator tag to the HTML
	 *
	 * @param string $html The HTML content.
	 * @return string
	 */
	public function add_meta_generator( $html ): string {
		if ( rocket_bypass() ) {
			return $html;
		}

		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return $html;
		}

		/**
		 * Filters whether to disable the WP Rocket meta generator tag.
		 *
		 * @since 3.17.2
		 *
		 * @param bool $disable True to disable, false otherwise.
		 */
		if ( wpm_apply_filters_typed( 'boolean', 'rocket_disable_meta_generator', false ) ) {
			return $html;
		}

		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_FOOTPRINT', false ) ) {
			return $this->remove_features_comments( $html );
		}

		if ( is_user_logged_in() ) {
			return $this->remove_features_comments( $html );
		}

		if ( false === preg_match_all( '/<!-- (?<feature>wpr_(?:[^-]*)) -->/i', $html, $comments, PREG_PATTERN_ORDER ) ) {
			return $html;
		}

		$meta = $this->get_meta_tag( $comments['feature'] );

		if ( empty( $meta ) ) {
			return $html;
		}

		$result = preg_replace( '/<\/head>/i', $meta . '</head>', $html, 1 );

		if ( null === $result ) {
			return $html;
		}

		return $this->remove_features_comments( $result );
	}

	/**
	 * Get the WP Rocket meta generator tag
	 *
	 * @param array $features Features to add to the meta tag.
	 *
	 * @return string
	 */
	private function get_meta_tag( array $features = [] ): string {
		if ( $this->options->get( 'do_caching_mobile_files', 0 ) ) {
			$features[] = $this->mobile_detect->isMobile() ? 'wpr_mobile' : 'wpr_desktop';
		}

		$dns_prefetch = rocket_get_dns_prefetch_domains();

		if (
				(
					! $this->options->get( 'cdn', 0 )
					&&
					! empty( $dns_prefetch )
				)
				||
				(
					$this->options->get( 'cdn', 0 )
					&&
					count( $dns_prefetch ) > 1
				)
			) {
			$features[] = 'wpr_dns_prefetch';
		}

		if ( (bool) $this->options->get( 'preload_links', 0 ) ) {
			$features[] = 'wpr_preload_links';
		}

		if ( empty( $features ) ) {
			return '';
		}

		$content = '';

		/**
		 * Filters the display of the content attribute in the meta generator tag.
		 *
		 * @since 3.17.2
		 *
		 * @param bool $display True to display, false otherwise.
		 */
		if ( wpm_apply_filters_typed( 'boolean', 'rocket_display_meta_generator_content', true ) ) {
			$content = ' content="WP Rocket ' . rocket_get_constant( 'WP_ROCKET_VERSION', '' ) . '"';
		}

		$meta = '<meta name="generator"' . $content . ' data-wpr-features="' . implode( ' ', $features ) . '" />';

		return $meta;
	}

	/**
	 * Remove WP Rocket features comments from the HTML
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	private function remove_features_comments( $html ): string {
		$result = preg_replace( '/<!-- wpr_[^-]* -->/i', '', $html );

		if ( null === $result ) {
			return $html;
		}

		return $result;
	}
}
