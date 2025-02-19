<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Queries\PreloadFonts as PFQuery;
use WP_Rocket\Engine\Media\PreloadFonts\Context\Context;
use WP_Rocket\Engine\Optimization\UrlTrait;

class Controller implements ControllerInterface {
	use UrlTrait;

	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options; // @phpstan-ignore-line Use of this will come later.

	/**
	 * Queries instance
	 *
	 * @var PFQuery
	 */
	private $query; // @phpstan-ignore-line Use of this will come later.

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context; // @phpstan-ignore-line Use of this will come later.

	/**
	 * Constructor
	 *
	 * @param Options_Data $options Options instance.
	 * @param PFQuery      $query Queries instance.
	 * @param Context      $context Context instance.
	 */
	public function __construct( Options_Data $options, PFQuery $query, Context $context ) {
		$this->options = $options;
		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Applies optimization.
	 *
	 * @param string $html HTML content.
	 * @param object $row Database Row.
	 *
	 * @return string
	 */
	public function optimize( string $html, $row ): string {
		if ( ! $row->has_preload_fonts() ) {
			return $html;
		}

		$html = $this->preload_fonts( $html, $row );

		return $html;
	}

	/**
	 * Add custom data like the List of elements to be considered for optimization.
	 *
	 * @param array $data Array of data passed in beacon.
	 *
	 * @return array
	 */
	public function add_custom_data( array $data ): array {
		$system_fonts = [
			'serif',
			'sans-serif',
			'monospace',
			'cursive',
			'fantasy',
			'system-ui',
			'ui-serif',
			'ui-sans-serif',
			'ui-monospace',
			'ui-rounded',
			'Arial',
			'Helvetica',
			'Times New Roman',
			'Times',
			'Courier New',
			'Courier',
			'Georgia',
			'Palatino',
			'Garamond',
			'Bookman',
			'Tahoma',
			'Trebuchet MS',
			'Arial Black',
			'Impact',
			'Comic Sans MS',
		];

		/**
		 * Filters the list of system fonts to be excluded from optimization.
		 *
			 * @param array $system_fonts Array of system fonts.
		 */
		$system_fonts = wpm_apply_filters_typed( 'array', 'rocket_preload_fonts_system_fonts', $system_fonts );

		$data['system_fonts'] = $system_fonts;

		return $data;
	}

	/**
	 * Preload Fonts in HTML.
	 *
	 * @param string $html HTML content.
	 *  @param object $row Corresponding DB row.
	 *
	 * @return string
	 */
	private function preload_fonts( string $html, $row ): string {
		if ( 'completed' !== $row->status || empty( $row->fonts ) || '[]' === $row->fonts ) {
			return $html;
		}

		if ( ! preg_match( '#</title\s*>#', $html, $matches ) ) {
			return $html;
		}
		$title = $matches[0];

		$preload = $title;

		$fonts = json_decode( $row->fonts, true );

		if ( empty( $fonts ) ) {
			return $html;
		}

		foreach ( $fonts as $font ) {
			$preload .= $this->preload_tag( $font );
		}

		$replace = preg_replace( '#' . $title . '#', $preload, $html, 1 );

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Checks if the font URL is from a third party.
	 *
	 * @param string $font_url Font URL.
	 *
	 * @return bool
	 */
	private function is_third_party_font( string $font_url ): bool {
		$parsed_url = wp_parse_url( $font_url );

		if ( empty( $parsed_url['host'] ) ) {
			return false;
		}

		$site_url = wp_parse_url( site_url() );

		return $parsed_url['host'] !== $site_url['host'];
	}

	/**
	 * Generates the preload tag for a font.
	 *
	 * @param string $font Font URL.
	 *
	 * @return string
	 */
	private function preload_tag( string $font ): string {
		if ( ! $this->is_relative( $font ) ) {
			$crossorigin = $this->is_third_party_font( $font ) ? ' crossorigin' : '';
			$tag         = sprintf(
				'<link rel="preload" data-rocket-preload as="font" href="%s"%s>',
				esc_url( $font ),
				$crossorigin
			);
		} else {
			$tag = sprintf(
				'<link rel="preload" data-rocket-preload as="font" href="%s">',
				$font
			);
		}
		return $tag;
	}
}
