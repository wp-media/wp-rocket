<?php

namespace WP_Rocket\Engine\Optimization\GoogleFonts;

use WP_Rocket\Logger\Logger;

/**
 * Combine Google Fonts
 *
 * @since  3.1
 */
class Combine extends AbstractGFOptimization {
	/**
	 * Found fonts
	 *
	 * @since  3.1
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $fonts = '';

	/**
	 * Found subsets
	 *
	 * @since  3.1
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $subsets = '';

	/**
	 * Combines multiple Google Fonts links into one
	 *
	 * @since  3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function optimize( $html ) {
		Logger::info( 'GOOGLE FONTS COMBINE PROCESS STARTED.', [ 'GF combine process' ] );

		$html_nocomments = $this->hide_comments( $html );
		$fonts           = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css[^\d](?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

		if ( ! $fonts ) {
			Logger::debug( 'No Google Fonts found.', [ 'GF combine process' ] );

			return $html;
		}

		$num_fonts = count( $fonts );

		Logger::debug(
			"Found {$num_fonts} Google Fonts.",
			[
				'GF combine process',
				'tags' => $fonts,
			]
		);

		if ( 1 === $num_fonts ) {
			return str_replace( $fonts[0][0], $this->get_font_with_display( $fonts[0] ), $html );
		}

		$this->parse( $fonts );

		if ( empty( $this->fonts ) ) {
			Logger::debug( 'No Google Fonts left to combine.', [ 'GF combine process' ] );

			return $html;
		}

		$html = preg_replace( '@<\/title>@i', '$0' . $this->get_combine_tag(), $html, 1 );

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
	 * Parses found matches to extract fonts and subsets.
	 *
	 * @since  3.1
	 * @author Remy Perona
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
	 * Returns the combined Google fonts link tag
	 *
	 * @since  3.3.5 Add support for the display parameter
	 * @since  3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	private function get_combine_tag() {
		return sprintf(
			'<link rel="stylesheet" href="%s" />', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			esc_url( "https://fonts.googleapis.com/css?family={$this->fonts}{$this->subsets}&display=swap" )
		);
	}
}
