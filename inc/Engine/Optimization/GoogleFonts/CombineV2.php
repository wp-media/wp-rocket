<?php

declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\GoogleFonts;

use WP_Rocket\Logger\Logger;

/**
 * Combine v2 Google Fonts
 *
 * @since  3.8
 */
class CombineV2 extends AbstractGFOptimization {

	/**
	 * Allowed display values.
	 *
	 * @since 3.8
	 *
	 * @var array
	 */
	protected $display_values = [
		'auto'     => 1,
		'block'    => 1,
		'swap'     => 1,
		'fallback' => 1,
		'optional' => 1,
	];

	/**
	 * Combines multiple Google Fonts (API v2) links into one
	 *
	 * @since  3.8
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function optimize( string $html ): string {
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

		$num_tags = count( $font_tags );

		Logger::debug(
			"Found {$num_tags} v2 Google Fonts.",
			[
				'GF combine process',
				'tags' => $font_tags,
			]
		);

		if ( 1 === $num_tags ) {
			return str_replace( $font_tags[0][0], $this->get_font_with_display( $font_tags[0] ), $html );
		}

		$families = [];
		foreach ( $font_tags as $tag ) {
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

		$families     = array_unique( $families );
		$combined_tag = $this->get_combine_tag( $families );
		$html         = preg_replace( '@<\/title>@i', '$0' . $combined_tag, $html, 1 );

		foreach ( $processed_tags as $font ) {
			$html = str_replace( $font[0], '', $html );
		}

		Logger::info(
			'V2 Google Fonts successfully combined.',
			[
				'GF combine process',
				'url' => $combined_tag,
			]
		);

		return $html;
	}

	/**
	 * Parses found matches to extract fonts and subsets.
	 *
	 * @since  3.8
	 * @param  array $tag A Google Font v2 url.
	 * @return array
	 */
	protected function parse( array $tag ) {
		if ( false !== strpos( $tag['url'], 'text=' ) ) {
			Logger::debug( 'GOOGLEFONTS V2 COMBINE: ' . $tag['url'] . ' SKIPPED TO PRESERVE "text" ATTRIBUTE.' );
			return [];
		}

		$url_pattern     = '#^(family=[A-Za-z0-9;:,=%&\+\@\.]+)$#';
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
	 * Returns the combined Google fonts link tag.
	 *
	 * @since  3.8
	 * @param  array $families Array with all Google V2 families.
	 * @return string
	 */
	protected function get_combine_tag( array $families ): string {
		return sprintf(
			'<link rel="stylesheet" href="%s" />', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			esc_url( "https://fonts.googleapis.com/css2{$this->get_concatenated_families( $families )}&display=swap" )
		);
	}

	/**
	 * Get a string of the concatenated font family queries.
	 *
	 * @since  3.8
	 * @param  array $families Array with all Google V2 families.
	 * @return string
	 */
	protected function get_concatenated_families( array $families ): string {
		$families_string = '?';
		foreach ( $families as $family ) {
			$families_string .= $family . '&';
		}

		return rtrim( $families_string, '&?' );
	}
}
