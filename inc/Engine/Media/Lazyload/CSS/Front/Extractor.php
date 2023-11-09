<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

use WP_Rocket\Engine\Optimization\RegexTrait;

class Extractor {

	use RegexTrait;

	/**
	 * Comment mapping.
	 *
	 * @var array
	 */
	protected $comments_mapping = [];

	/**
	 * Extract background images from CSS.
	 *
	 * @param string $content CSS content.
	 * @return array
	 */
	public function extract( string $content ): array {

		$this->comments_mapping = [];

		$comment_regex = '#/\*[^*]*\*+([^/][^*]*\*+)*/#';

		$content = preg_replace_callback(
			$comment_regex,
			function ( $matches ) {
				$id                            = '/*' . uniqid( 'bg_css_comment' ) . '*/';
				$this->comments_mapping[ $id ] = $matches[0];
				return $id;
			},
			$content
			);

		$background_regex = '(?<selector>(?:[ \-,:\w.()\n\r^>[*"\'=\]#]|(?:\[[^\]]+\]))+)\s?{[^{}]*background\s*:(?<property>[^;}]*)[^}]*}';

		$background_image_regex = '(?<selector>(?:[ \-,:\w.()\n\r^>[*"\'=\]#]|(?:\[[^\]]+\]))+)\s?{[^{}]*background-image\s*:(?<property>[^;}]*)[^}]*}';

		/**
		 * Lazyload background property regex.
		 *
		 * @param string $regex Lazyload background property regex.
		 */
		$background_regex = $this->apply_string_filter( 'lazyload_css_extract_bg_property_regex', $background_regex );

		/**
		 * Lazyload background property regex.
		 *
		 * @param string $regex Lazyload background property regex.
		 */
		$background_image_regex = $this->apply_string_filter( 'lazyload_css_extract_bg_img_property_regex', $background_image_regex );

		$background_matches       = $this->find( $background_regex, $content, 'mi' );
		$background_image_matches = $this->find( $background_image_regex, $content, 'mi' );

		$matches = array_merge( $background_matches, $background_image_matches );

		if ( empty( $matches ) ) {
			return [];
		}

		$results = [];

		foreach ( $matches as $match ) {
			if ( ! key_exists( 'property', $match ) || ! key_exists( 'selector', $match ) ) {
				continue;
			}

			$property = $match['property'];
			$selector = $match['selector'];

			$property = trim( $property );
			$selector = trim( $selector );

			$urls  = $this->extract_urls( $property );
			$block = trim( $match[0] );
			foreach ( $this->comments_mapping as $id => $comment ) {
				$block = str_replace( $id, $comment, $block );
			}

			foreach ( $urls as $url ) {
				$results[ $selector ][] = [
					'selector' => $selector,
					'url'      => $url['url'],
					'original' => $url['original'],
					'block'    => $block,
				];
			}
		}

		return $results;
	}

	/**
	 * Extract URLS from a CSS property.
	 *
	 * @param string $content Content from the CSS property.
	 * @return array
	 */
	protected function extract_urls( string $content ): array {

		/**
		 * Lazyload URL regex.
		 *
		 * @param string $regex Lazyload URL regex.
		 */
		$regex = $this->apply_string_filter( 'lazyload_css_extract_url_regex', '(?<tag>url\([\'"]?(?<url>[^\)]*)[\'"]?\))' );

		$matches = $this->find( $regex, $content, 'mi' );

		$output = [];

		/**
		 * Lazyload ignored URLs.
		 *
		 * @param string[] $urls Ignored URLs.
		 */
		$ignored_urls = (array) apply_filters( 'rocket_lazyload_css_ignored_urls', [] );

		foreach ( $matches as $match ) {

			$url = $match['url'] ?: '';
			$url = str_replace( '"', '', $url );
			$url = str_replace( "'", '', $url );
			$url = trim( $url );
			$url = $this->make_url_complete( $url );
			if ( ! key_exists( 'tag', $match ) || ! key_exists( 'url', $match ) || ! $url || $this->is_url_ignored( $url, $ignored_urls ) ) {
				continue;
			}

			if ( ! $this->is_url_external( $url ) && ! $this->is_relative( $url ) ) {
				$url = $this->apply_string_filter( 'css_url', $url );
			}

			$url          = "url('$url')";
			$original_url = trim( $match['tag'], ' ,' );
			$output[]     = [
				'url'      => $url,
				'original' => $original_url,
			];
		}

		return $output;
	}

	/**
	 * Apply a string filter.
	 *
	 * @param string $name Name from the filter.
	 * @param string $value Value to pass to the filter that will be returned.
	 * @param array  ...$args Additional values.
	 * @return string
	 */
	protected function apply_string_filter( string $name, string $value, ...$args ) {
		$output = apply_filters( 'rocket_' . $name, $value,  ...$args );

		if ( ! is_string( $output ) ) {
			return $value;
		}

		return $output;
	}

	/**
	 * Check if the URLs is ignored.
	 *
	 * @param string $url URL to check.
	 * @param array  $ignored_urls List of ignored URLs.
	 *
	 * @return bool
	 */
	protected function is_url_ignored( string $url, array $ignored_urls ): bool {
		foreach ( $ignored_urls as $ignored_url ) {
			if ( strpos( $url, $ignored_url ) !== false ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Complete the URL if necessary.
	 *
	 * @param string $url URL to complete.
	 *
	 * @return string
	 */
	protected function make_url_complete( string $url ): string {
		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( $host || $this->is_relative( $url ) ) {
			return $url;
		}

		return rocket_get_home_url() . '/' . trim( $url, '/ ' );
	}

	/**
	 * Check if the URL is external.
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool
	 */
	protected function is_url_external( string $url ): bool {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host ) {
			return false;
		}

		$home_host = wp_parse_url( rocket_get_home_url(), PHP_URL_HOST );

		return $host !== $home_host;
	}

	/**
	 * Check if the URL is relative.
	 *
	 * @param string $url URL to check.
	 * @return bool
	 */
	protected function is_relative( string $url ): bool {
		return preg_match( '/^\./', $url );
	}
}
