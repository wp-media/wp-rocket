<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Engine\Optimization\UrlTrait;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface;

class Controller implements ControllerInterface {
	use RegexTrait;
	use UrlTrait;

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
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options Options instance.
	 * @param ATFQuery     $query Queries instance.
	 * @param Context      $context Context instance.
	 */
	public function __construct( Options_Data $options, ATFQuery $query, Context $context ) {
		$this->options = $options;
		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Optimize the LCP image
	 *
	 * @param string $html HTML content.
	 * @param object $row Database Row.
	 *
	 * @return string
	 */
	public function optimize( string $html, $row ): string {
		if ( ! $row->has_lcp() ) {
			return $html;
		}

		return $this->preload_lcp( $html, $row );
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
		if ( rocket_bypass() ) { // Bail out if rocket_bypass() returns true.
			return $html;
		}

		if ( ! preg_match( '#</title\s*>#', $html, $matches ) ) {
			return $html;
		}

		$title   = $matches[0];
		$preload = $title;

		$lcp = json_decode( $row->lcp );

		$preload .= $this->preload_tag( $lcp );

		$replace = preg_replace( '#' . $title . '#', $preload, $html, 1 );

		if ( null === $replace ) {
			return $html;
		}

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
	 * Alters the preload element tag (img|img-srcset)
	 *
	 * @param object $lcp LCP object.
	 * @param string $html HTML content.
	 * @return string
	 */
	private function set_fetchpriority( $lcp, string $html ): string {
		$allowed_types = [
			'img',
			'img-srcset',
			'picture',
		];

		if ( empty( (array) $lcp ) || empty( $lcp->type ) || ! in_array( $lcp->type, $allowed_types, true ) ) {
			return $html;
		}

		if ( empty( $lcp->src ) ) {
			return $html;
		}

		$html    = $this->replace_html_comments( $html );
		$url     = urldecode( preg_quote( $lcp->src, '/' ) );
		$pattern = '#<img(?:[^>]*?\s+)?src=["\']' . $url . '["\'](?:\s+[^>]*?)?>#';
		if ( wp_http_validate_url( $lcp->src ) && ! $this->is_external_file( $lcp->src ) ) {
			$url = preg_quote(
				wp_parse_url( $lcp->src, PHP_URL_PATH ),
			'/'
				);

			$pattern = '#<img(?:[^>]*?\s+)?src\s*=\s*["\'](?:https?:)?(?:\/\/(?:[^\/]+)\/?)?\/?' . $url . '["\'](?:\s+[^>]*?)?>#i';
		}

		$html = preg_replace_callback(
			$pattern,
			function ( $matches ) {
				// Check if the fetchpriority attribute already exists.
				if ( preg_match( '/<img[^>]*\sfetchpriority(?:\s*=\s*["\'][^"\']*["\'])?[^>]*>/i', $matches[0] ) ) {
					// If it exists, don't modify the tag.
					return $matches[0];
				}

				// If it doesn't exist, add the fetchpriority attribute.
				$replace = preg_replace( '/<img/', '<img fetchpriority="high"', $matches[0] );

				if ( null === $replace ) {
					return $matches[0];
				}

				return $replace;
			},
			$html,
			1
		);

		return $this->restore_html_comments( $html );
	}

	/**
	 * Add above the fold images to lazyload exclusions
	 *
	 * @param array $exclusions Array of excluded patterns.
	 *
	 * @return array
	 */
	public function add_exclusions( $exclusions ): array {
		if ( ! $this->context->is_allowed() ) {
			return $exclusions;
		}

		list($atf, $lcp) = [ [], [] ];

		global $wp;

		$url = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );

		$row = $this->query->get_row( $url, $this->is_mobile() );

		if ( ! $row ) {
			return $exclusions;
		}

		if ( $row->lcp && 'not found' !== $row->lcp ) {
			$lcp = $this->generate_lcp_link_tag_with_sources( json_decode( $row->lcp ) );
			$lcp = $lcp['sources'];
			$lcp = $this->get_path_for_exclusion( $lcp );
		}

		if ( $row->viewport && 'not found' !== $row->viewport ) {
			$atf = $this->get_atf_sources( json_decode( $row->viewport ) );
			$atf = $this->get_path_for_exclusion( $atf );
		}

		$exclusions = array_merge( $exclusions, $lcp, $atf );

		// Remove lcp candidate from the atf array.
		$exclusions = array_unique( $exclusions );

		return $exclusions;
	}

	/**
	 * Get only the url path to exclude.
	 *
	 * @param array $exclusions Array of exclusions.
	 * @return array
	 */
	private function get_path_for_exclusion( array $exclusions ): array {
		$exclusions = array_map(
				function ( $exclusion ) {
					$exclusion = wp_parse_url( $exclusion );
					return ltrim( $exclusion['path'], '/' );
				},
			$exclusions
			);

		return $exclusions;
	}

	/**
	 * Generate preload link tags with sources for LCP.
	 *
	 * @param object $lcp LCP Object.
	 * @return array
	 */
	private function generate_lcp_link_tag_with_sources( object $lcp ): array {
		$pairs = [
			'tags'    => '',
			'sources' => [],
		];

		$tag       = '';
		$start_tag = '<link rel="preload" data-rocket-preload as="image" ';
		$end_tag   = ' fetchpriority="high">';

		$sources = [];

		switch ( $lcp->type ) {
			case 'img':
				$sources[] = $lcp->src;
				$tag      .= $start_tag . 'href="' . ( $this->is_relative( $lcp->src ) ? esc_attr( $lcp->src ) : esc_url( $lcp->src ) ) . '"' . $end_tag;
				break;
			case 'img-srcset':
				$sources[] = $lcp->src;
				$tag      .= $start_tag . 'href="' . ( $this->is_relative( $lcp->src ) ? esc_attr( $lcp->src ) : esc_url( $lcp->src ) ) . '" imagesrcset="' . esc_attr( $lcp->srcset ) . '" imagesizes="' . esc_attr( $lcp->sizes ) . '"' . $end_tag;
				break;
			case 'bg-img-set':
				foreach ( $lcp->bg_set as $set ) {
					$sources[] = $set->src;
				}

				$tag .= $start_tag . 'imagesrcset="' . esc_attr( implode( ',', $sources ) ) . '"' . $end_tag;
				break;
			case 'bg-img':
				foreach ( $lcp->bg_set as $set ) {
					$sources[] = $set->src;

					$tag .= $start_tag . 'href="' . ( $this->is_relative( $set->src ) ? esc_attr( $set->src ) : esc_url( $set->src ) ) . '"' . $end_tag;
				}
				break;
			case 'picture':
				$result  = $this->generate_source_tags( $lcp, $start_tag, $end_tag );
				$sources = $result['sources'];
				$tag    .= $result['tag'];
				break;
		}

		$pairs['tags']    = $tag;
		$pairs['sources'] = $sources;

		return $pairs;
	}

	/**
	 * Get above the fold images sources.
	 *
	 * @param array $atfs Above the fold object.
	 * @return array
	 */
	private function get_atf_sources( array $atfs ): array {
		$sources = [];

		foreach ( $atfs as $atf ) {
			switch ( $atf->type ) {
				case 'img':
				case 'img-srcset':
					$sources[] = $atf->src;
					break;
				case 'bg-img-set':
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
	 * @return bool
	 */
	private function is_mobile(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&& $this->options->get( 'do_caching_mobile_files', 0 )
			&& wp_is_mobile();
	}

	/**
	 * Generates the source tags for the given LCP object.
	 *
	 * This method is used to generate the source tags for the given LCP object. It iterates over the sources of the LCP object,
	 * and for each source, it generates a media query and adds the source to the sources array. It also constructs a tag string
	 * with the source and media query. If a previous max-width is found, it is used to update the media query. The method also
	 * handles the case where a max-width is found in the source's media attribute.
	 *
	 * @param object $lcp The LCP object containing the sources.
	 * @param string $start_tag The starting tag for each source.
	 * @param string $end_tag The ending tag for each source.
	 *
	 * @return array An associative array containing the sources array and the tag string.
	 */
	private function generate_source_tags( $lcp, $start_tag, $end_tag ) {
		$prev_max_width = null;
		$sources        = [];
		$tag            = '';
		$prev_type      = null;

		// Iterate over the sources in the LCP object.
		foreach ( $lcp->sources as $i => $source ) {
			// If the type of the previous source is not equal to the type of the current source, break the loop.
			if ( ! empty( $source->type ) && $prev_type !== $source->type && null !== $prev_type ) {
				break;
			}

			$media = ! empty( $source->media ) ? $source->media : '';

			// If a previous max-width is found, update the media query.
			if ( null !== $prev_max_width && false === strpos( $media, 'min-width' ) ) {
				$media = '(min-width: ' . ( $prev_max_width + 0.1 ) . 'px) and ' . $media;
			}

			// Add the media attribute to the media string.

			$media = ! empty( $media ) ? ' media="' . $media . '"' : '';

			$sources[] = $source->srcset;
			// Get the sizes attribute of the source, if it exists.
			$sizes = ! empty( $source->sizes ) ? ' imagesizes="' . $source->sizes . '"' : '';

			// Determine whether to use 'href' or 'imagesrcset' based on the srcset attribute.
			$link_attribute = ( substr_count( $source->srcset, ',' ) > 0 ) ? 'imagesrcset' : 'href';

			// Append the source and media query to the tag string.
			$tag .= $start_tag . $link_attribute . '="' . $source->srcset . '"' . ( $media ) . $sizes . $end_tag;

			// If a max-width is found in the source's media attribute, update the previous max-width.
			if ( preg_match( '/\(max-width: (\d+(\.\d+)?)px\)/', $source->media, $matches ) ) {
				$prev_max_width = floatval( $matches[1] );
			}

			$prev_type = $source->type;
		}

		// If a previous max-width is found, update the media query and add the LCP source to the sources array and the tag string.
		if ( null !== $prev_max_width ) {
			$media     = ' media="(min-width: ' . ( $prev_max_width + 0.1 ) . 'px)"';
			$sources[] = $lcp->src;
			$tag      .= $start_tag . 'href="' . $lcp->src . '"' . $media . $end_tag;
		}

		// Return an associative array containing the sources array and the tag string.
		return [
			'sources' => $sources,
			'tag'     => $tag,
		];
	}

	/**
	 * Add custom data like the comma-separated list of elements
	 * to be considered for the lcp/above-the-fold optimization.
	 *
	 * @param array $data Array of data passed in beacon.
	 *
	 * @return array
	 */
	public function add_custom_data( array $data ): array {
		$elements = [
			'img',
			'video',
			'picture',
			'p',
			'main',
			'div',
			'li',
			'svg',
			'section',
			'header',
			'span',
		];

		$default_elements = $elements;

		/**
		 * Filters the array of elements
		 *
		 * @since 3.16
		 *
		 * @param array $formats Array of elements
		 */
		$elements = wpm_apply_filters_typed( 'array', 'rocket_atf_elements', $default_elements );

		$elements = array_filter( $elements, 'is_string' );

		$data['elements']      = implode( ', ', $elements );
		$data['status']['atf'] = $this->context->is_allowed();

		return $data;
	}
}
