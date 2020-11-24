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

		$processed_tags  = [];
		$html_nocomments = $this->hide_comments( $html );
		$font_tags       = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css2(?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

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
			if ( $this->parse( $tag ) ) {
				$processed_tags[] = $tag;
			}
		}

		if ( empty( $this->families ) ) {
			Logger::debug( 'No v2 Google Fonts left to combine.', [ 'GF combine process' ] );

			return $html;
		}

		$html = preg_replace( '@<\/title>@i', '$0' . $this->get_combine_tag(), $html, 1 );
		foreach ( $processed_tags as $font ) {
			$html = str_replace( $font[0], '', $html );
		}

		Logger::info(
			'V2 Google Fonts successfully combined.',
			[
				'GF combine process',
				'url' => '...', // todo: insert the generated combined url here.
			]
		);

		return $html;
	}

	/**
	 * Parses found matches to extract fonts and subsets.
	 *
	 * @since  3.8
	 *
	 * @param array $tag A Google Font v2 url.
	 *
	 * @return bool
	 */
	protected function parse( array $tag ): bool {
		if ( false !== strpos( $tag['url'], 'text=' ) ) {
			Logger::debug( 'GOOGLEFONTS V2 COMBINE: ' . $tag['url'] . ' SKIPPED TO PRESERVE "text" ATTRIBUTE.' );
			return false;
		}

		$url_pattern     = '#^(family=[A-Za-z0-9;:,=%&\+\@\.]+)$#';
		$display_pattern = '#&display=(?:swap|auto|block|fallback|optional)#';
		$decoded_url     = html_entity_decode( $tag['url'] );
		$query           = wp_parse_url( $decoded_url, PHP_URL_QUERY );

		if ( empty( $query ) ) {
			return false;
		}

		if ( ! preg_match_all( $url_pattern, $query, $matches, PREG_PATTERN_ORDER ) ) {
			return false;
		}

		foreach ( $matches[1] as $family ) {
			$this->families[] = preg_replace( $display_pattern, '', $family );
		}

		return true;
	}


	/**
	 * Returns the combined Google fonts link tag.
	 *
	 * @since  3.8

	 * @return string
	 */
	protected function get_combine_tag(): string {
		$display = $this->get_font_display_value();

		return sprintf(
			'<link rel="stylesheet" href="%s" />', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			esc_url( "https://fonts.googleapis.com/css2{$this->get_concatenated_families()}&display={$display}" )
		);
	}

	/**
	 * Returns font with display value.
	 *
	 * @since  3.8
	 *
	 * @param array $font Array containing font tag and matches.
	 *
	 * @return string Google Font tag with display param.
	 */
	protected function get_font_with_display( array $font ): string {
		$font_url = html_entity_decode( $font['url'] );
		$query    = wp_parse_url( $font_url, PHP_URL_QUERY );

		if ( empty( $query ) ) {
			return $font[0];
		}

		$display     = $this->get_font_display_value();
		$parsed_font = wp_parse_args( $query );
		$font_url    = ! empty( $parsed_font['display'] )
			? str_replace( "&display={$parsed_font['display']}", "&display={$display}", $font_url )
			: "{$font_url}&display={$display}";

		return str_replace( $font['url'], esc_url( $font_url ), $font[0] );
	}

	/**
	 * Get the font display value.
	 *
	 * @since  3.8
	 *
	 * @return string font display value.
	 */
	protected function get_font_display_value(): string {
		/**
		 * Filters the combined Google Fonts display parameter value.
		 *
		 * This filter is documented in GoogleFonts\Combine::get_font_display_value().
		 *
		 * @param string $display Display value. Can be either auto, block, swap, fallback or optional.
		 */
		$display = apply_filters( 'rocket_combined_google_fonts_display', 'swap' );
		if ( ! is_string( $display ) ) {
			return 'swap';
		}

		return isset( $this->display_values[ $display ] ) ? $display : 'swap';
	}

	/**
	 * Get a string of the concatenated font family queries.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	protected function get_concatenated_families(): string {
		$families = '?';

		foreach ( $this->families as $family ) {
			$families .= $family . '&';
		}

		return esc_url( rtrim( $families, '&?' ) );
	}
}
