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

		$old_regex = '(?<selector>[ \-\w.\n#]+)\s?{[^}]*background(-image)?\s*:(?<property>[^;]*)[^}]*}';

		/**
		 * Lazyload property regex.
		 *
		 * @param string $regex Lazyload property regex.
		 */
		$regex = apply_filters( 'rocket_lazyload_css_extract_property_regex', $old_regex );

		if ( ! is_string( $regex ) ) {
			$regex = $old_regex;
		}

		$matches = $this->find( $regex, $content, 'mi' );

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

			$urls = $this->extract_urls( $property );

			$block = trim( $match[0] );
			foreach ( $this->comments_mapping as $id => $comment ) {
				$block = str_replace( $id, $comment, $block );
			}

			foreach ( $urls as $url ) {
				$results[ $selector ][] = [
					'selector' => $selector,
					'url'      => $url,
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

		$old_regex = '(?<tag>url\([\'"]?(?<url>[^\)]*)[\'"]?\))';
		/**
		 * Lazyload URL regex.
		 *
		 * @param string $regex Lazyload URL regex.
		 */
		$regex = apply_filters( 'rocket_lazyload_css_extract_url_regex', $old_regex );

		if ( ! is_string( $regex ) ) {
			$regex = $old_regex;
		}

		$matches = $this->find( $regex, $content, 'mi' );

		$output = [];

		foreach ( $matches as $match ) {
			if ( ! key_exists( 'tag', $match ) || ! key_exists( 'url', $match ) ) {
				continue;
			}
			$url      = $match['tag'];
			$url      = trim( $url, ' ,' );
			$output[] = $url;
		}

		return $output;
	}
}
