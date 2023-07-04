<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

use WP_Rocket\Engine\Optimization\RegexTrait;

class Extractor {

	use RegexTrait;

	/**
	 * Extract background images from CSS.
	 *
	 * @param string $content CSS content.
	 * @return array
	 */
	public function extract( string $content ): array {

		 $matches = $this->find( '(?<selector>[ \-\w.#]+)\s?{[^}]*background(-image)?\s*:(?<property>[^;]*)[^}]*}', $content, 'mi' );

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

			foreach ( $urls as $url ) {
				$results[] = [
					'selector' => $selector,
					'url' => $url,
					'block' => $match[0]
				];
			}
		}

		return $results;
	}

	protected function extract_urls( string $content ): array {
		$matches = $this->find( '(?<tag>url\([\'"]?(?<url>[^\)]*)[\'"]?\))', $content, 'mi' );

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
