<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\GoogleFonts;

use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Logger\Logger;

/**
 * Combine Google Fonts
 *
 * @since  3.1
 */
class Combine extends AbstractGFOptimization {
	use RegexTrait;

	/**
	 * Found fonts
	 *
	 * @since 3.1
	 *
	 * @var string
	 */
	protected $fonts = '';

	/**
	 * Found subsets
	 *
	 * @since 3.1
	 *
	 * @var string
	 */
	protected $subsets = '';

	/**
	 * Font urls.
	 *
	 * @var array
	 */
	protected $font_urls = [];

	/**
	 * Combines multiple Google Fonts links into one
	 *
	 * @since  3.1
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function optimize( $html ): string {
		$this->font_urls = [];
		Logger::info( 'GOOGLE FONTS COMBINE PROCESS STARTED.', [ 'GF combine process' ] );

		$html_nocomments = $this->hide_comments( $html );
		$fonts           = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css[^\d](?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

		if ( ! $fonts ) {
			Logger::debug( 'No Google Fonts found.', [ 'GF combine process' ] );

			$this->has_google_fonts = false;

			return $html;
		}

		$this->has_google_fonts = true;

		$exclusions = $this->get_exclusions();

		$filtered_fonts = array_filter(
			$fonts,
			function ( $font ) use ( $exclusions ) {
				return ! $this->is_excluded( $font[0], $exclusions );
			}
		);

		$num_fonts = count( $filtered_fonts );

		Logger::debug(
			"Found {$num_fonts} Google Fonts after exclusions.",
			[
				'GF combine process',
				'tags' => $filtered_fonts,
			]
		);

		$this->parse( $filtered_fonts );

		if ( empty( $this->fonts ) ) {
			Logger::debug( 'No Google Fonts left to combine.', [ 'GF combine process' ] );

			return $html;
		}

		$this->font_urls[] = $this->get_combined_url();

		foreach ( $filtered_fonts as $font ) {
			$html = str_replace( $font[0], '', $html );
		}

		Logger::info(
			'Google Fonts successfully combined.',
			[
				'GF combine process',
				'url' => $this->fonts . $this->subsets,
			]
		);

		return $html;
	}

	/**
	 * Parses found matches to extract fonts and subsets.
	 *
	 * @since  3.1
	 *
	 * @param array $matches Found matches for the pattern.
	 *
	 * @return void
	 */
	private function parse( array $matches ) {
		$fonts_array   = [];
		$subsets_array = [];
		foreach ( $matches as $match ) {
			$url   = html_entity_decode( $match[2] );
			$query = wp_parse_url( $url, PHP_URL_QUERY );
			if ( empty( $query ) ) {
				return;
			}

			$font = wp_parse_args( $query );
			if ( isset( $font['family'] ) ) {
				$font_family = $font['family'];
				$font_family = rtrim( $font_family, '%7C' );
				$font_family = rtrim( $font_family, '|' );
				// Add font to the collection.
				$fonts_array[] = rawurlencode( htmlentities( $font_family ) );
			}

			// Add subset to collection.
			if ( isset( $font['subset'] ) ) {
				$subsets_array[] = rawurlencode( htmlentities( $font['subset'] ) );
			}
		}

		// Concatenate fonts tag.
		$this->subsets = ! empty( $subsets_array ) ? '&subset=' . implode( ',', array_filter( array_unique( $subsets_array ) ) ) : '';
		$this->fonts   = ! empty( $fonts_array ) ? implode( '%7C', array_filter( array_unique( $fonts_array ) ) ) : '';
	}

	/**
	 * Returns the combined Google fonts URL
	 *
	 * @since  3.9.1
	 *
	 * @return string
	 */
	private function get_combined_url(): string {
		$display = $this->get_font_display_value();

		return esc_url( "https://fonts.googleapis.com/css?family={$this->fonts}{$this->subsets}&display={$display}" );
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
