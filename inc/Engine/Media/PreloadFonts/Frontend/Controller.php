<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Queries\PreloadFonts as PFQuery;
use WP_Rocket\Engine\Media\PreloadFonts\Context\Context;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Rows\PreloadFonts;
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
		if ( ! $this->context->is_allowed() || ! $row->has_preload_fonts() ) {
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

		$data['preload_fonts_exclusions'] = $this->context->get_exclusions();
		$data['status']['preload_fonts']  = $this->context->is_allowed();
		$data['processed_extensions']     = $this->context->get_extensions();

		/**
		 * Filters the external font domains to exclude from processing
		 *
		 * @since 3.19.1
		 *
		 * @param array $exclusions Array of domains to exclude from external font processing
		 */
		$data['external_font_exclusions'] = wpm_apply_filters_typed( 'string[]', 'rocket_external_font_exclusions', [] );

		return $data;
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

		$row = $this->get_current_url_row();
		if ( empty( $row ) ) {
			return $items;
		}

		$fonts = json_decode( $row->fonts, true );

		if ( empty( $fonts ) ) {
			return $items;
		}

		foreach ( $fonts as $font ) {
			$item_args = [
				'href' => esc_url( $font ),
				'as'   => 'font',
				2      => 'crossorigin',
			];

			$items[] = $this->preload_link( $item_args );
		}

		return $items;
	}

	/**
	 * Get current visited page row in DB.
	 *
	 * @return false|PreloadFonts
	 */
	private function get_current_url_row() {
		global $wp;

		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->context->is_mobile_allowed();

		$row = $this->query->get_row( $url, $is_mobile );
		if ( empty( $row ) || 'completed' !== $row->status || empty( $row->fonts ) || '[]' === $row->fonts ) {
			return false;
		}
		return $row;
	}

	/**
	 * Disables the Remove Unused CSS (RUCSS) feature for preloading fonts.
	 *
	 * This method can be used as a filter callback to control whether the RUCSS feature
	 * should be applied when preloading fonts.
	 *
	 * @param bool $status Current status of the RUCSS preload fonts feature.
	 * @return bool Modified status indicating whether RUCSS should be disabled for preloading fonts.
	 */
	public function disable_rucss_preload_fonts( $status ) {
		if ( ! $this->context->is_allowed() ) {
			return $status;
		}

		$row = $this->get_current_url_row();
		if ( empty( $row ) ) {
			return $status;
		}

		return false;
	}

	/**
	 * Removes existing preloaded font links from the provided HTML content if necessary.
	 *
	 * This method scans the given HTML string and removes any <link rel="preload" as="font"> tags
	 * that match certain criteria, to prevent duplicate or unnecessary font preloads.
	 * Only removes preloaded fonts when WP Rocket's font optimization is actually applied.
	 *
	 * @param string $html The HTML content to process.
	 * @return string The modified HTML content with preloaded font links removed if applicable.
	 */
	public function maybe_remove_existing_preloaded_fonts( string $html ): string {
		if ( ! $this->context->is_allowed() ) {
			return $html;
		}
		$row = $this->get_current_url_row();
		if ( ! $row ) {
			return $html;
		}

		/**
		 * Filter to enable or disable deleting existing preloaded tags.
		 *
		 * @param bool $should_remove
		 */
		$should_remove = wpm_apply_filters_typed( 'boolean', 'rocket_remove_existing_preloaded_fonts', true );
		if ( ! $should_remove ) {
			return $html;
		}

		// One regex to skip scripts and remove any <link rel=preload as=font…> tag (entire line, including indentation and newline):.
		$html = preg_replace(
			'#<script\b[^>]*>[\s\S]*?<\/script\b[^>]*>(*SKIP)(*FAIL)'    // skip <script> blocks.
			. '|^[ \t]*<link\b'                                           // OR match a <link at line start (with optional indent).
			. '(?=[^>]*\brel\s*=\s*(["\']?)preload\1)'                 // lookahead rel=preload.
			. '(?=[^>]*\bas\s*=\s*(["\']?)font\2)'                     // lookahead as=font.
			. '[^>]*?\/?>[ \t]*(?:\r?\n|$)#im',                        // up to /> or >, then trim whitespace and newline.
			'',
			$html
		);

		return $html;
	}
}
