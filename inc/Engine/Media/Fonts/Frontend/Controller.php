<?php

namespace WP_Rocket\Engine\Media\Fonts\Frontend;

use WP_Rocket\Engine\Media\Fonts\Context\Context;
use WP_Rocket\Engine\Media\Fonts\Factory\GoogleFontFactory;

class Controller {
	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param Context $context Context instance.
	 */
	public function __construct( Context $context ) {
		$this->context = $context;
	}

	/**
	 * Rewrites the Google Fonts paths to local ones.
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function rewrite_fonts( string $html ): string {
		if ( ! $this->context->is_allowed() ) {
			return $html;
		}

		// Use the URL hash value to get the CSS file paths.
		$pattern = '/<link\s+[^>]*href=["\'](https:\/\/fonts\.googleapis\.com\/css2?[^"\']+)["\'][^>]*>/i';
		$html    = preg_replace_callback(
			$pattern,
			function ( $matches ) {
				$google_fonts_url = $matches[1];
				$font_factory     = new GoogleFontFactory( $google_fonts_url );
				$font_instance    = $font_factory->get_font_version();

				if ( ! $font_instance ) {
					return $matches[0];
				}

				$local_url = $font_instance->get_local_url();
				return str_replace( $google_fonts_url, $local_url, $matches[0] ) . ' data-wpr-hosted-gf-parameters="' . esc_attr( wp_parse_url( $google_fonts_url, PHP_URL_QUERY ) ) . '"';
			},
			$html
			);

		return $html;
	}
}
