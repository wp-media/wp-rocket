<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Provider\GoogleFont;

use WP_Rocket\Engine\Optimization\RegexTrait;

class CSSHandler {
	use RegexTrait;

	public function get_font_from_html( $html ): array {
		$font_urls  = [];
		$clean_html = $this->hide_comments( $html );
		$fonts      = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css[^\d](?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $clean_html );

		if ( empty( $fonts ) ) {
			return [];
		}

		foreach ( $fonts as $font ) {
			if ( isset( $font['url'] ) ) {
				$font_urls[] = $font['url'];
			}
		}

		return $font_urls;
	}
}
