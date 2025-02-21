<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\GoogleFonts;

use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Logger\Logger;

/**
 * Combine v2 Google Fonts
 *
 * @since  3.8
 */
class CombineV2 extends AbstractGFOptimization {
	use RegexTrait;

	/**
	 * Font urls.
	 *
	 * @var array
	 */
	protected $font_urls = [];

	/**
	 * Combines multiple Google Fonts (API v2) links into one
	 *
	 * @since  3.8
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function optimize( $html ): string {
		$this->font_urls = [];
		Logger::info( 'GOOGLE FONTS COMBINE-V2 PROCESS STARTED.', [ 'GF combine process' ] );

		$processed_tags  = [];
		$html_nocomments = $this->hide_comments( $html );
		$font_tags       = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css2(?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

		if ( ! $font_tags ) {
			Logger::debug( 'No v2 Google Fonts found.', [ 'GF combine process' ] );

			$this->has_google_fonts = false;

			return $html;
		}

		$this->has_google_fonts = true;

		$exclusions = $this->get_exclusions();

		$filtered_tags = array_filter(
			$font_tags,
			function ( $tag ) use ( $exclusions ) {
				return ! $this->is_excluded( $tag[0], $exclusions );
			}
			);

		$num_tags = count( $filtered_tags );

		Logger::debug(
			"Found {$num_tags} v2 Google Fonts after exclusions.",
			[
				'GF combine process',
				'tags' => $filtered_tags,
			]
		);

		$families = [];
		foreach ( $filtered_tags as $tag ) {
			$parsed_families = $this->parse( $tag );
			if ( ! empty( $parsed_families ) ) {
				$processed_tags[] = $tag;
				$families         = array_merge( $families, $parsed_families );
			}
		}

		if ( empty( $families ) ) {
			Logger::debug( 'No v2 Google Fonts left to combine.', [ 'GF combine process' ] );
			return $html;
		}

		$families          = array_unique( $families );
		$combined_url      = $this->get_combined_url( $families );
		$this->font_urls[] = $combined_url;

		foreach ( $processed_tags as $font ) {
			$html = str_replace( $font[0], '', $html );
		}

		Logger::info(
			'V2 Google Fonts successfully combined.',
			[
				'GF combine process',
				'url' => $combined_url,
			]
		);

		return $html;
	}

	/**
	 * Parses found matches to extract fonts and subsets.
	 *
	 * @since  3.8
	 *
	 * @param  array $tag A Google Font v2 url.
	 *
	 * @return array
	 */
	private function parse( array $tag ): array {
		if ( false !== strpos( $tag['url'], 'text=' ) ) {
			Logger::debug( 'GOOGLEFONTS V2 COMBINE: ' . $tag['url'] . ' SKIPPED TO PRESERVE "text" ATTRIBUTE.' );
			return [];
		}

		$url_pattern     = '#(family=[A-Za-z0-9;:,=%&\+\@\.]+)$#';
		$display_pattern = '#&display=(?:swap|auto|block|fallback|optional)#';
		$decoded_url     = html_entity_decode( $tag['url'] );
		$query           = wp_parse_url( $decoded_url, PHP_URL_QUERY );

		if ( empty( $query ) ) {
			return [];
		}

		if ( ! preg_match_all( $url_pattern, $query, $matches, PREG_PATTERN_ORDER ) ) {
			return [];
		}

		$families = [];
		foreach ( $matches[1] as $family ) {
			$families[] = preg_replace( $display_pattern, '', $family );
		}

		return $families;
	}


	/**
	 * Returns the combined Google fonts URL
	 *
	 * @since  3.9.1
	 *
	 * @param  array $families Array with all Google V2 families.
	 *
	 * @return string
	 */
	private function get_combined_url( array $families ): string {
		$display = $this->get_font_display_value();

		return esc_url( "https://fonts.googleapis.com/css2{$this->get_concatenated_families( $families )}&display={$display}" );
	}

	/**
	 * Get a string of the concatenated font family queries.
	 *
	 * @since  3.8
	 *
	 * @param  array $families Array with all Google V2 families.
	 *
	 * @return string
	 */
	private function get_concatenated_families( array $families ): string {
		$families_string = '?';
		foreach ( $families as $family ) {
			$families_string .= $family . '&';
		}

		return rtrim( $families_string, '&?' );
	}

	/**
	 * Get font urls, getter method for font_urls property.
	 *
	 * @return array
	 */
	public function get_font_urls(): array {
		return $this->font_urls;
	}

	/**
	 * Insert font stylesheets into head.
	 *
	 * @param array $items Head elements.
	 * @return mixed
	 */
	public function insert_font_stylesheet_into_head( $items ) {
		$font_urls = $this->get_font_urls();
		if ( empty( $font_urls ) ) {
			return $items;
		}

		return $this->prepare_stylesheet_fonts_to_head( $font_urls, $items );
	}

	/**
	 * Insert font preloads into head.
	 *
	 * @param array $items Head elements.
	 * @return mixed
	 */
	public function insert_font_preload_into_head( $items ) {
		$font_urls = $this->get_font_urls();
		if ( empty( $font_urls ) ) {
			return $items;
		}

		if ( ! $this->is_preload_enabled() ) {
			return $items;
		}

		return $this->prepare_preload_fonts_to_head( $font_urls, $items );
	}
}
