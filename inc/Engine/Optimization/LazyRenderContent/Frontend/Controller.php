<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Queries\LazyRenderContent as LRCQuery;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\Processor;

class Controller implements ControllerInterface {
	/**
	 * Processor instance
	 *
	 * @var Processor
	 */
	private $processor;

	/**
	 * Context instance
	 *
	 * @var ContextInterface
	 */
	private $context;

	/**
	 * LRCQuery instance
	 *
	 * @var LRCQuery
	 */
	private $query;

	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Processor        $processor Processor instance.
	 * @param ContextInterface $context Context instance.
	 * @param LRCQuery         $query Query instance.
	 * @param Options_Data     $options Options instance.
	 */
	public function __construct( Processor $processor, ContextInterface $context, LRCQuery $query, Options_Data $options ) {
		$this->processor = $processor;
		$this->context   = $context;
		$this->query     = $query;
		$this->options   = $options;
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
		if ( ! $row->has_lrc() ) {
			return $html;
		}

		$hashes = json_decode( $row->below_the_fold );

		if ( null === $hashes || ! is_array( $hashes ) ) {
			return $html;
		}

		$result = preg_replace( '/data-rocket-location-hash="(?:"' . implode( '|', $hashes ) . ')"/i', 'data-wpr-lazyrender="1"', $html );

		if ( null === $result ) {
			return $html;
		}

		$html = $result;
		$html = $this->remove_hashes( $html );

		return $this->add_css( $html );
	}

	/**
	 * Remove hashes from the HTML content.
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	private function remove_hashes( $html ) {
		$result = preg_replace( '/data-rocket-location-hash="(?:.*)"/i', '', $html );

		if ( null === $result ) {
			return $html;
		}

		return $result;
	}

	/**
	 * Add CSS to the HTML content.
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	private function add_css( $html ) {
		$css = '<style>[data-wpr-lazyrender] {
  content-visibility: auto;
}</style>';

		$result = preg_replace( '/<\/head>/i', $css . '</head>', $html, 1 );

		if ( null === $result ) {
			return $html;
		}

		return $result;
	}

	/**
	 * Add hashes to the HTML elements
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	public function add_hashes( $html ) {
		global $wp;

		if ( ! $this->context->is_allowed() ) {
			return $html;
		}

		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->is_mobile();

		if ( $this->query->get_row( $url, $is_mobile ) ) {
			return $html;
		}

		/**
		 * Filters the Lazy Render Content processor to use.
		 *
		 * @since 3.17
		 *
		 * @param string $processor The processor to use.
		 */
		$processor = wpm_apply_filters_typed( 'string', 'rocket_lrc_processor', 'dom' );

		$this->processor->set_processor( $processor );

		return $this->processor->get_processor()->add_hashes( $html );
	}

	/**
	 * Add custom data like the List of elements to be considered for optimization.
	 *
	 * @param array $data Array of data passed in beacon.
	 *
	 * @return array
	 */
	public function add_custom_data( array $data ): array {
		$elements = [
			'div',
			'main',
			'footer',
			'section',
			'article',
			'header',
		];

		/**
		 * Filters the array of elements
		 *
		 * @since 3.17
		 *
		 * @param array $formats Array of elements
		 */
		$elements = wpm_apply_filters_typed( 'string', 'rocket_lrc_elements', $elements );

		$data['lrc_elements']  = implode( ', ', $elements );
		$data['status']['lrc'] = $this->context->is_allowed();

		return $data;
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return bool
	 */
	private function is_mobile(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&& $this->options->get( 'do_caching_mobile_files', 0 )
			&& wp_is_mobile();
	}
}
