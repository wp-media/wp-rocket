<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Queries\PreloadFonts as PFQuery;
use WP_Rocket\Engine\Media\PreloadFonts\Context\Context;
use WP_Rocket\Engine\Optimization\UrlTrait;
use WP_Rocket\Engine\Support\CommentTrait;
use WP_Rocket\Engine\Common\Head\ElementTrait;

class Controller implements ControllerInterface {
	use UrlTrait;
	use CommentTrait;
	use ElementTrait;

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
	private $context;

	private $fonts_tags = [];

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
		if ( ! $this->context->is_allowed() ) {
			return $html;
		}
		return $this->add_meta_comment( 'auto_preload_fonts', $html );
	}

	/**
	 * Add custom data like the List of elements to be considered for optimization.
	 *
	 * @param array $data Array of data passed in beacon.
	 *
	 * @return array
	 */
	public function add_custom_data( array $data ): array {
		if ( ! $this->context->is_allowed() ) {
			return $data;
		}

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
	 * Adds the preload fonts to the head tag.
	 *
	 * @param array $items added to the head.
	 * @return array Items to be added to the head.
	 */
	public function add_preload_fonts_in_head( $items ) {
		if ( ! $this->context->is_allowed() ) {
			return $items;
		}

		global $wp;

		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->context->is_mobile_allowed();

		$row = $this->query->get_row( $url, $is_mobile );
		if ( empty( $row ) || 'completed' !== $row->status || empty( $row->fonts ) || '[]' === $row->fonts ) {
			return $items;
		}

		$fonts = json_decode( $row->fonts, true );

		if ( empty( $fonts ) ) {
			return $items;
		}

		foreach ( $fonts as $font ) {
			$item_args = [
				//'id'         => 'preload-font-' . md5( $font ), // Unique ID based on font URL.
				'href'       => esc_url( $font ),
				'as'         => 'font',
			];

			if ( ! $this->is_relative( $font ) && $this->is_third_party_font( $font ) ) {
				$item_args[ 2 ] = 'crossorigin';
			}

			$items[] = $this->preload_link( $item_args );
		}

		return $items;
	}
}
