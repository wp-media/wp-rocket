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
	 * Font Families
	 *
	 * @since  3.8
	 *
	 * @var array
	 */
	protected $families = [];

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

		$html_nocomments = $this->hide_comments( $html );
		$font_tags   = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css2(?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

		if ( ! $font_tags ) {
			Logger::debug( 'No v2 Google Fonts found.', [ 'GF combine process' ] );
			return $html;
		}

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

		foreach ( $font_tags as $tag ) {
			$this->parse( $tag['url'] );
		}

		if ( empty( $this->families ) ) {
			Logger::debug( 'No v2 Google Fonts left to combine.', [ 'GF combine process' ] );

			return $html;
		}

		$combined = $this->get_combine_tag();
		$html = preg_replace( '@<\/title>@i', '$0' . $combined, $html, 1 );

		foreach ( $font_tags as $font ) {
			$html = str_replace( $font[0], '', $html );
		}

		Logger::info(
			'V2 Google Fonts successfully combined.',
			[
				'GF combine process',
				'url' => $combined,
			]
		);

		return $html;
	}

	/**
	 * Parses APIv2 URLs to extract font-family strings.
	 *
	 * @since  3.8
	 *
	 * @param string $url A Google Font v2 url.
	 *
	 * @return void
	 */
	private function parse( string $url ): void {
		$url_pattern = '#^(family=[A-Za-z0-9;:,=%&\+\@\.]+)$#';
		$display_pattern = '#&display=(?:swap|auto|block|fallback|optional)#';

		$decoded_url = html_entity_decode( $url ); //return $decoded_url;
		$query       = wp_parse_url( $decoded_url, PHP_URL_QUERY );

		if ( empty( $query ) ) {
			return;
		}

		if ( ! preg_match_all( $url_pattern, $query, $matches, PREG_PATTERN_ORDER ) ) {
			return;
		}

		foreach ( $matches[1] as $family ) {
			$this->families[] = preg_replace( $display_pattern, '', $family );
		}
	}


	/**
	 * Returns the combined Google fonts link tag.
	 *
	 * @since  3.8

	 * @return string
	 */
	private function get_combine_tag(): string {
		$display = $this->get_font_display_value();

		return sprintf(
			'<link rel="stylesheet" href="%s" />', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			esc_url( "https://fonts.googleapis.com/css2{$this->get_concatenated_families()}&display={$display}" )
		);
	}

	/**
	 * Get a string of the concatenated font family queries.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	private function get_concatenated_families(): string {
		$families = '?';

		foreach ( $this->families as $family ) {
			$families .= $family . '&';
		}

		return esc_url( rtrim( $families, '&?' ) );
	}
}
