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

		if ( empty( $row ) ) {
			return $html;
		}

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
		$preload = '</title>';

		$lcp = json_decode( $row->lcp );

		$preload .= $this->preload_tag( $lcp );

		$replace = preg_replace( '#</title>#', $preload, $html, 1 );
		$replace = $this->set_fetchpriority( $lcp, $replace );

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
		$tags = $this->generate_lcp_link_tag_with_sources( $lcp );
		return $tags['tags'];
	}

	/**
	 * Alters the preload element tag(img|img-srcset)
	 *
	 * @param object        $lcp LCP object.
	 * @param string string $html HTML content.
	 * @return string
	 */
	private function set_fetchpriority( $lcp, string $html ): string {
		if ( 'img' !== $lcp->type && 'img-srcset' !== $lcp->type && 'picture' !== $lcp->type ) {
			return $html;
		}

		$url  = preg_quote( $lcp->src, '/' );
		$html = preg_replace( '/(<img[^>]*\s+src="' . $url . '+")/', '$1 fetchpriority="high"', $html, 1 );

		return $html;
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

		if ( ! $row ) {
			return $exclusions;
		}

		$lcp = $this->generate_lcp_link_tag_with_sources( json_decode( $row->lcp ) );
		$atf = $this->get_atf_sources( json_decode( $row->viewport ) );

		$exclusions = array_merge( $exclusions, $lcp['sources'], $atf );

		// Remove lcp candidate from the atf array.
		$exclusions = array_unique( $exclusions );

		return $exclusions;
	}

	/**
	 * Generate preload link tags with sources for LCP.
	 *
	 * @param object $lcp LCP Object.
	 * @return array
	 */
	private function generate_lcp_link_tag_with_sources( $lcp ): array {
		$tag       = '';
		$start_tag = '<link rel="preload" as="image" ';
		$end_tag   = ' fetchpriority="high">';

		$sources = [];

		switch ( $lcp->type ) {
			case 'img':
				$sources[] = $lcp->src;
				$tag      .= $start_tag . 'href="' . $lcp->src . '"' . $end_tag;
				break;
			case 'img-srcset':
				$sources[] = $lcp->src;
				$tag      .= $start_tag . 'href="' . $lcp->src . '" imagesrcset="' . $lcp->srcset . '" imagesizes="' . $lcp->sizes . '"' . $end_tag;
				break;
			case 'bg-img-set':
				foreach ( $lcp->bg_set as $set ) {
					$sources[] = $set->src;
				}

				$tag .= $start_tag . 'imagesrcset="' . implode( ',', $sources ) . '"' . $end_tag;
				break;
			case 'bg-img':
				foreach ( $lcp->bg_set as $set ) {
					$sources[] = $set->src;

					$tag .= $start_tag . 'href="' . $set->src . '"' . $end_tag;
				}
				break;
			case 'picture':
				if ( ! empty( $lcp->sources ) ) {
					foreach ( $lcp->sources as $source ) {
						$sources[] = $source->srcset;
						$tag      .= $start_tag . 'href="' . $source->srcset . '" media="' . $source->media . '"' . $end_tag;
					}
				}
				$sources[] = $lcp->src;
				$tag      .= $start_tag . 'href="' . $lcp->src . '"' . $end_tag;
				break;
		}

		return [
			'tags'    => $tag,
			'sources' => $sources,
		];
	}

	/**
	 * Get above the fold images sources.
	 *
	 * @param array $atfs Above the fold object.
	 * @return array
	 */
	private function get_atf_sources( array $atfs ): array {
		if ( ! is_array( $atfs ) ) {
			return [];
		}

		$sources = [];

		foreach ( $atfs as $atf ) {
			switch ( $atf->type ) {
				case 'img':
					$sources[] = $atf->src;
					break;
				case 'img-srcset':
					$sources[] = $atf->src;
					break;
				case 'bg-img-set':
					foreach ( $atf->bg_set as $set ) {
						$sources[] = $set->src;
					}
					break;
				case 'bg-img':
					foreach ( $atf->bg_set as $set ) {
						$sources[] = $set->src;
					}
					break;
				case 'picture':
					if ( ! empty( $atf->sources ) ) {
						foreach ( $atf->sources as $source ) {
							$sources[] = $source->srcset;
						}
					}
					$sources[] = $atf->src;
					break;
			}
		}

		return $sources;
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
