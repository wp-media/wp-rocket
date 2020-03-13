<?php
namespace WP_Rocket\Optimization\CSS;

use WP_Rocket\Logger\Logger;
use WP_Rocket\Optimization\Abstract_Optimization;

/**
 * Combine Google Fonts
 *
 * @since 3.1
 * @author Remy Perona
 */
class Combine_Google_Fonts extends Abstract_Optimization {
	/**
	 * Found fonts
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $fonts = '';

	/**
	 * Found subsets
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $subsets = '';

	/**
	 * Combines multiple Google Fonts links into one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		Logger::info( 'GOOGLE FONTS COMBINE PROCESS STARTED.', [ 'GF combine process' ] );

		$html_nocomments = $this->hide_comments( $html );
		$fonts           = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css(?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

		if ( ! $fonts ) {
			Logger::debug( 'No Google Fonts found.', [ 'GF combine process' ] );
			return $html;
		}

		Logger::debug(
			'Found ' . count( $fonts ) . ' Google Fonts.',
			[
				'GF combine process',
				'tags' => $fonts,
			]
		);

		if ( 1 === count( $fonts ) ) {
			return str_replace( $fonts[0][0], $this->get_font_with_display( $fonts[0] ), $html );
		}

		$this->parse( $fonts );

		if ( empty( $this->fonts ) ) {
			Logger::debug( 'No Google Fonts left to combine.', [ 'GF combine process' ] );
			return $html;
		}

		$html = str_replace( '</title>', '</title>' . $this->get_combine_tag(), $html );

		foreach ( $fonts as $font ) {
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
	 * Finds links to Google fonts
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $pattern Pattern to search for.
	 * @param string $html HTML content.
	 * @return bool|array
	 */
	protected function find( $pattern, $html ) {
		$result = preg_match_all( '/' . $pattern . '/Umsi', $html, $matches, PREG_SET_ORDER );

		if ( empty( $result ) ) {
			return false;
		}

		return $matches;
	}

	/**
	 * Parses found matches to extract fonts and subsets
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Array $matches Found matches for the pattern.
	 * @return void
	 */
	protected function parse( $matches ) {
		$fonts_array   = [];
		$subsets_array = [];
		foreach ( $matches as $match ) {
			$url   = html_entity_decode( $match[2] );
			$query = wp_parse_url( $url, PHP_URL_QUERY );

			if ( ! isset( $query ) ) {
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
	 * Returns the combined Google fonts link tag
	 *
	 * @since 3.3.5 Add support for the display parameter
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	protected function get_combine_tag() {
		/**
		 * Filters the combined Google Fonts display parameter value
		 *
		 * @since 3.3.5
		 * @author Remy Perona
		 *
		 * @param string $display Display value. Can be either auto, block, swap, fallback or optional.
		 */
		$display        = apply_filters( 'rocket_combined_google_fonts_display', 'swap' );
		$allowed_values = $this->get_font_display_values();
		$display        = isset( $allowed_values[ $display ] ) ? $display : 'swap';

		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		return '<link rel="stylesheet" href="' . esc_url( 'https://fonts.googleapis.com/css?family=' . $this->fonts . $this->subsets . '&display=' . $display ) . '" />';
	}

	/**
	 * Returns font with display value.
	 *
	 * @since  3.5.1
	 * @author Soponar Cristina
	 *
	 * @param  array $font Array containing font tag and matches.
	 * @return string Google Font tag with display param.
	 */
	protected function get_font_with_display( array $font ) {
		$font_url = html_entity_decode( $font['url'] );

		$query = wp_parse_url( $font_url, PHP_URL_QUERY );

		if ( ! isset( $query ) ) {
			return $font[0];
		}

		// This filter is documented in inc/classes/optimization/CSS/class-combine-google-fonts.php.
		$display        = apply_filters( 'rocket_combined_google_fonts_display', 'swap' );
		$allowed_values = $this->get_font_display_values();
		$display        = isset( $allowed_values[ $display ] ) ? $display : 'swap';

		$parsed_font = wp_parse_args( $query );

		if ( empty( $parsed_font['display'] ) ) {
			// Add default display.
			return str_replace( $font['url'], esc_url( $font_url . '&display=' . $display ), $font[0] );
		}

		$font_url = str_replace( '&display=' . $parsed_font['display'], '&display=' . $display, $font_url );

		return str_replace( $font['url'], esc_url( $font_url ), $font[0] );
	}

	/**
	 * Returns array with font display accepted values
	 *
	 * @since  3.5.1
	 * @author Soponar Cristina
	 *
	 * @return array
	 */
	protected function get_font_display_values() {
		return [
			'auto'     => 1,
			'block'    => 1,
			'swap'     => 1,
			'fallback' => 1,
			'optional' => 1,
		];
	}
}
