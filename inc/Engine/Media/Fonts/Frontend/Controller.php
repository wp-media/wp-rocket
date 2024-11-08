<?php

namespace WP_Rocket\Engine\Media\Fonts\Frontend;

use WP_Rocket\Engine\Media\Fonts\Context\Context;
use WP_Rocket\Engine\Optimization\GoogleFonts\Combine;
use WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Logger\Logger;

class Controller {
	use RegexTrait;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	private $combineV1;

	private $combineV2;

	/**
	 * Constructor.
	 *
	 * @param Context $context Context instance.
	 */
	public function __construct( Context $context, Combine $combineV1, CombineV2 $combineV2 ) {
		$this->context   = $context;
		$this->combineV1 = $combineV1;
		$this->combineV2 = $combineV2;
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
		$html_nocomments = $this->hide_comments( $html );
		$v1_url          = $v2_url = '';

		$v1_fonts = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css[^\d](?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );
		$v2_fonts = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css2(?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

		if ( ! $v1_fonts && ! $v2_fonts ) {
			Logger::debug( 'No Google Fonts found.', [ 'Host Google Font' ] );
			return $html;
		}

		if ( $v1_fonts ) {
			$this->combineV1->parse( $v1_fonts );
		}

		$families = [];
		if ( $v2_fonts ) {
			foreach ( $v2_fonts as $tag ) {
				$parsed_families = $this->combineV2->parse( $tag );
				if ( ! empty( $parsed_families ) ) {
					$families = array_merge( $families, $parsed_families );
				}
			}

			$families = array_unique( $families );
			$this->combineV2->parse( $v2_fonts );
		}

		$v1_url = $this->combineV1->get_combined_url();
		if ( ! empty( $families ) ) {
			$v2_url = $this->combineV2->get_combined_url( $families );
		}

		// debug print
		return print_r( $v1_url, true ) . ' | ' . print_r( md5( $v1_url ), true ) . ' ||| ' . print_r( $v2_url, true ) . ' | ' . print_r( md5( $v2_url ), true );

		// Need to add check if font file is existing depending on the URL path.
		// If it exists => change HTML to that path.

		return $html;
	}
}
