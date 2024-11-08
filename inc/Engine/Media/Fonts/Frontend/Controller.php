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
		$v1_path         = $v2_path = '';

		$v1_fonts = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css[^\d](?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );
		$v2_fonts = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css2(?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

		if ( ! $v1_fonts && ! $v2_fonts ) {
			Logger::debug( 'No Google Fonts found.', [ 'Host Google Font' ] );
			return $html;
		}

		if ( $v1_fonts ) {
			$this->combineV1->parse( $v1_fonts );
			$v1_url  = $this->combineV1->get_combined_url();
			$v1_path = $this->get_local_path( $this->get_current_url(), md5( $v1_url ) );
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

		if ( ! empty( $families ) ) {
			$v2_url  = $this->combineV2->get_combined_url( $families );
			$v2_path = $this->get_local_path( $this->get_current_url(), md5( $v2_url ) );
		}

		if ( file_exists( $v1_path ) ) {
			$html = preg_replace( '@<\/title>@i', '$0' . $this->get_optimized_markup( $v1_path ), $html, 1 );
		}

		if ( file_exists( $v2_path ) ) {
			$html = preg_replace( '@<\/title>@i', '$0' . $this->get_optimized_markup( $v2_path ), $html, 1 );
		}

		return $html;
	}

	/**
	 * Get the local path for the combined URL.
	 *
	 * @param string $url The combined URL.
	 * @param string $filename The filename.
	 * @return string The local path.
	 */
	private function get_local_path( string $url, string $filename ): string {
		$parsed_url = parse_url( $url );

		$path       = ( ! empty( $parsed_url['path'] ) ) ? $parsed_url['path'] : '';
		$protocol   = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? 'https://' : 'http://';
		$host       = $_SERVER['HTTP_HOST'];
		$local_path = $protocol . $host . '/wp-content/cache/wp-rocket/fonts' . $path . '/' . $filename . '.css';

		return $local_path;
	}

	/**
	 * Get the current URL.
	 *
	 * @return string The current URL.
	 */
	private function get_current_url(): string {
		$protocol    = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? 'https://' : 'http://';
		$current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		return $current_url;
	}

	/**
	 * Returns the optimized markup for Google Fonts
	 *
	 * @since 3.18
	 *
	 * @param string $url Google Fonts URL.
	 *
	 * @return string
	 */
	protected function get_optimized_markup( string $url ): string {
		return sprintf(
			'<link rel="preload" data-rocket-preload as="style" href="%1$s" /><link rel="stylesheet" href="%1$s" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="%1$s" /></noscript>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			$url
		);
	}
}
