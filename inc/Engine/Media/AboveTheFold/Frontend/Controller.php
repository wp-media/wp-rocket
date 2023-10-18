<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Optimization\RegexTrait;

class Controller implements ContextInterface {
	use RegexTrait;

	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Queries instance
	 *
	 * @var ATFQuery
	 */
	private $query;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options Options instance.
	 * @param ATFQuery     $query Queries instance.
	 */
	public function __construct( Options_Data $options, ATFQuery $query ) {
		$this->options = $options;
		$this->query   = $query;
	}

	/**
	 * Determine if the action is allowed.
	 *
	 * @param array $data Data to pass to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		return apply_filters( 'rocket_above_the_fold_optimization', true );
	}

	/**
	 * Optimize the LCP image
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function lcp( $html ): string {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		global $wp;

		$url = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );

		$row = $this->query->get_row( $url, $this->is_mobile() );

		$html = $this->preload_lcp( $html, $row );

		return $html;
	}

	/**
	 * Preload the LCP image
	 *
	 * @param string $html HTML content.
	 * @param object $row Corresponding DB row.
	 *
	 * @return string
	 */
	private function preload_lcp( $html, $row ) {
		$pattern = '<head(?:[^>])>';
		$nodes   = $this->find( $pattern, $html );

		if ( empty( $nodes ) ) {
			return $html;
		}

		$preload = $this->preload_tag( $row->lcp );

		$replace = preg_replace( $pattern, $pattern . $preload, $html, 1 );

		if ( ! $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Builds the preload tag
	 *
	 * @param object $lcp LCP object.
	 *
	 * @return string
	 */
	private function preload_tag( $lcp ): string {
		$tag = '<link rel="preload" as="image" ';

		switch ( $lcp->type ) {
			case 'img':
			case 'bg-img':
				$tag .= 'href="' . $lcp->src . '"';
				break;
			case 'img-srcset':
				$tag .= 'href="' . $lcp->src . '" imagesrcset="' . $lcp->srcset . '" imagesizes="' . $lcp->sizes . '"';
				break;
			case 'picture':
				break;
			case 'bg-img-set':
				$sources = [];

				foreach ( $lcp->bg_set as $set ) {
					$sources[] = $set->src;
				}

				$tag .= 'imagesrcset="' . implode( ',', $sources ) . '"';
				break;
		}

		$tag .= ' fetchpriority="high">';

		return $tag;
	}

	/**
	 * Add above the fold images to lazyload exclusions
	 *
	 * @param array $exclusions Array of excluded patterns.
	 *
	 * @return array
	 */
	public function add_exclusions( $exclusions ): array {
		if ( ! $this->is_allowed() ) {
			return $exclusions;
		}

		global $wp;

		$url = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );

		$row = $this->query->get_row( $url, $this->is_mobile() );

		$lcp      = $row->lcp;
		$viewport = $row->viewport;

		return $exclusions;
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return boolean
	 */
	private function is_mobile(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&& $this->options->get( 'do_caching_mobile_files', 0 )
			&& wp_is_mobile();
	}
}
