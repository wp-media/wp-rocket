<?php
/**
 * Handles lazyloading of iframes
 *
 * @package WP_Rocket\Dependencies\RocketLazyload
 */

namespace WP_Rocket\Dependencies\RocketLazyload;

/**
 * A class to provide the methods needed to lazyload iframes in WP Rocket and Lazyload by WP Rocket
 */
class Iframe {

	/**
	 * Finds iframes in the HTML provided and call the methods to lazyload them
	 *
	 * @param string $html   Original HTML.
	 * @param string $buffer Content to parse.
	 * @param array  $args   Array of arguments to use.
	 * @return string
	 */
	public function lazyloadIframes( $html, $buffer, $args = [] ) {
		$defaults = [
			'youtube' => false,
		];

		$args = wp_parse_args( $args, $defaults );

		if ( ! preg_match_all( '@<iframe(?<atts>\s.+)>.*</iframe>@iUs', $buffer, $iframes, PREG_SET_ORDER ) ) {
			return $html;
		}

		$iframes = array_unique( $iframes, SORT_REGULAR );

		foreach ( $iframes as $iframe ) {
			if ( $this->isIframeExcluded( $iframe ) ) {
				continue;
			}

			// Given the previous regex pattern, $iframe['atts'] starts with a whitespace character.
			if ( ! preg_match( '@\ssrc\s*=\s*(\'|")(?<src>.*)\1@iUs', $iframe['atts'], $atts ) ) {
				continue;
			}

			$iframe['src'] = trim( $atts['src'] );

			if ( '' === $iframe['src'] ) {
				continue;
			}

			if ( $args['youtube'] ) {
				$iframe_lazyload = $this->replaceYoutubeThumbnail( $iframe );
			}

			if ( empty( $iframe_lazyload ) ) {
				$iframe_lazyload = $this->replaceIframe( $iframe );
			}

			$html = str_replace( $iframe[0], $iframe_lazyload, $html );

			unset( $iframe_lazyload );
		}

		return $html;
	}

	/**
	 * Checks if the provided iframe is excluded from lazyload
	 *
	 * @param array $iframe Array of matched patterns.
	 * @return boolean
	 */
	public function isIframeExcluded( $iframe ) {

		foreach ( $this->getExcludedPatterns() as $excluded_pattern ) {
			if ( strpos( $iframe[0], $excluded_pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets patterns excluded from lazyload for iframes
	 *
	 * @since 2.1.1
	 *
	 * @return array
	 */
	private function getExcludedPatterns() {
		/**
		 * Filters the patterns excluded from lazyload for iframes
		 *
		 * @since 2.1.1
		 *
		 * @param array $excluded_patterns Array of excluded patterns.
		 */
		return apply_filters(
			'rocket_lazyload_iframe_excluded_patterns',
			[
				'gform_ajax_frame',
				'data-no-lazy=',
				'recaptcha/api/fallback',
				'loading="eager"',
				'data-skip-lazy',
				'skip-lazy',
			]
		);
	}

	/**
	 * Applies lazyload on the iframe provided
	 *
	 * @param array $iframe Array of matched elements.
	 * @return string
	 */
	private function replaceIframe( $iframe ) {
		/**
		 * Filter the LazyLoad placeholder on src attribute
		 *
		 * @since 1.0
		 *
		 * @param string $placeholder placeholder that will be printed.
		 */
		$placeholder = apply_filters( 'rocket_lazyload_placeholder', 'about:blank' );

		$placeholder_atts = str_replace( $iframe['src'], $placeholder, $iframe['atts'] );
		$iframe_lazyload  = str_replace( $iframe['atts'], $placeholder_atts . ' data-rocket-lazyload="fitvidscompatible" data-lazy-src="' . esc_url( $iframe['src'] ) . '"', $iframe[0] );

		if ( ! preg_match( '@\sloading\s*=\s*(\'|")(?:lazy|auto)\1@i', $iframe_lazyload ) ) {
			$iframe_lazyload = str_replace( '<iframe', '<iframe loading="lazy"', $iframe_lazyload );
		}

		/**
		 * Filter the LazyLoad HTML output on iframes
		 *
		 * @since 1.0
		 *
		 * @param array $html Output that will be printed.
		 */
		$iframe_lazyload  = apply_filters( 'rocket_lazyload_iframe_html', $iframe_lazyload );
		$iframe_lazyload .= '<noscript>' . $iframe[0] . '</noscript>';

		return $iframe_lazyload;
	}

	/**
	 * Replaces the iframe provided by the Youtube thumbnail
	 *
	 * @param array $iframe Array of matched elements.
	 * @return bool|string
	 */
	private function replaceYoutubeThumbnail( $iframe ) {
		$youtube_id = $this->getYoutubeIDFromURL( $iframe['src'] );

		if ( ! $youtube_id ) {
			return false;
		}

		$query = wp_parse_url( htmlspecialchars_decode( $iframe['src'] ), PHP_URL_QUERY );

		$youtube_url = $this->changeYoutubeUrlForYoutuDotBe( $iframe['src'] );
		$youtube_url = $this->cleanYoutubeUrl( $iframe['src'] );
		/**
		 * Filter the LazyLoad HTML output on Youtube iframes
		 *
		 * @since 2.11
		 *
		 * @param array $html Output that will be printed.
		 */
		$youtube_lazyload  = apply_filters( 'rocket_lazyload_youtube_html', '<div class="rll-youtube-player" data-src="' . esc_attr( $youtube_url ) . '" data-id="' . esc_attr( $youtube_id ) . '" data-query="' . esc_attr( $query ) . '"></div>' );
		$youtube_lazyload .= '<noscript>' . $iframe[0] . '</noscript>';

		return $youtube_lazyload;
	}

	/**
	 * Gets the Youtube ID from the URL provided
	 *
	 * @param string $url URL to search.
	 * @return bool|string
	 */
	public function getYoutubeIDFromURL( $url ) {
		$pattern = '#^(?:https?:)?(?://)?(?:www\.)?(?:youtu\.be|youtube\.com|youtube-nocookie\.com)/(?:embed/|v/|watch/?\?v=)?([\w-]{11})#iU';
		$result  = preg_match( $pattern, $url, $matches );

		if ( ! $result ) {
			return false;
		}

		// exclude playlist.
		if ( 'videoseries' === $matches[1] ) {
			return false;
		}

		return $matches[1];
	}

	/**
	 * Changes URL youtu.be/ID to youtube.com/embed/ID
	 *
	 * @param  string $url URL to replace.
	 * @return string      Unchanged URL or modified URL.
	 */
	public function changeYoutubeUrlForYoutuDotBe( $url ) {
		$pattern = '#^(?:https?:)?(?://)?(?:www\.)?(?:youtu\.be)/(?:embed/|v/|watch/?\?v=)?([\w-]{11})#iU';
		$result  = preg_match( $pattern, $url, $matches );

		if ( ! $result ) {
			return $url;
		}

		return 'https://www.youtube.com/embed/' . $matches[1];
	}

	/**
	 * Cleans Youtube URL. Keeps only scheme, host and path.
	 *
	 * @param  string $url URL to be cleaned.
	 * @return string      Cleaned URL
	 */
	public function cleanYoutubeUrl( $url ) {
		$parsed_url = wp_parse_url( $url, -1 );
		$scheme     = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '//';
		$host       = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
		$path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';

		return $scheme . $host . $path;
	}
}
