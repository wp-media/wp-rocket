<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Frontend;

use WP_Filesystem_Direct;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Optimization\RegexTrait;

class Controller {
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
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * WordPress filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Constructor
	 *
	 * @param Options_Data              $options Options instance.
	 * @param ATFQuery                  $query Queries instance.
	 * @param Context                   $context Context instance.
	 * @param WP_Filesystem_Direct|null $filesystem WordPress filesystem.
	 */
	public function __construct( Options_Data $options, ATFQuery $query, Context $context, WP_Filesystem_Direct $filesystem = null ) {
		$this->options    = $options;
		$this->query      = $query;
		$this->context    = $context;
		$this->filesystem = $filesystem ?: rocket_direct_filesystem();
	}

	/**
	 * Optimize the LCP image
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function lcp( $html ): string {
		if ( ! $this->context->is_allowed() ) {
			return $html;
		}

		global $wp;

		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->is_mobile();
		$row       = $this->query->get_row( $url, $is_mobile );

		if ( empty( $row ) ) {
			return $this->inject_beacon( $html, $url, $is_mobile );
		}

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
		if ( ! preg_match( '#</title\s*>#', $html, $matches ) ) {
			return $html;
		}

		$title   = $matches[0];
		$preload = $title;

		$lcp = json_decode( $row->lcp );

		$preload .= $this->preload_tag( $lcp );

		$replace = preg_replace( '#' . $title . '#', $preload, $html, 1 );
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

		if ( ! in_array( $lcp->type, $allowed_types, true ) ) {
			return $html;
		}

		$url  = preg_quote( $lcp->src, '/' );
		$html = preg_replace_callback(
			'#<img(?:[^>]*?\s+src=["\']' . $url . '["\'][^>]*?|[^>]*?)>#',
			function ( $matches ) {
				// Check if the fetchpriority attribute already exists.
				if ( preg_match( '/fetchpriority\s*=\s*[\'"]([^\'"]+)[\'"]/i', $matches[0] ) ) {
					// If it exists, don't modify the tag.
					return $matches[0];
				}

				// If it doesn't exist, add the fetchpriority attribute.
				return preg_replace( '/<img/', '<img fetchpriority="high"', $matches[0] );
			},
			$html,
			1
		);

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
					return $exclusion['path'];
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
	private function generate_lcp_link_tag_with_sources( $lcp ): array {
		$pairs = [
			'tags'    => '',
			'sources' => [],
		];

		if ( ! $lcp && ! is_object( $lcp ) ) {
			return $pairs;
		}

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
		if ( ! $atfs && ! is_array( $atfs ) ) {
			return [];
		}

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
	 * The `inject_beacon` function is used to inject a JavaScript beacon into the HTML content.
	 *
	 * @param string $html The HTML content where the beacon will be injected.
	 * @param string $url The current URL.
	 * @param bool   $is_mobile True for mobile device, false otherwise.
	 *
	 * @return string The modified HTML content with the beacon script injected just before the closing body tag.
	 */
	public function inject_beacon( $html, $url, $is_mobile ): string {
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if ( ! $this->filesystem->exists( rocket_get_constant( 'WP_ROCKET_ASSETS_JS_PATH' ) . 'lcp-beacon' . $min . '.js' ) ) {
			return $html;
		}

		$data = [
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'rocket_lcp' ),
			'url'       => $url,
			'is_mobile' => $is_mobile,
		];

		$inline_script = '<script>var rocket_lcp_data = ' . wp_json_encode( $data ) . '</script>';

		// Get the URL of the script.
		$script_url = rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'lcp-beacon' . $min . '.js';

		// Create the script tag.
		$script_tag = "<script src='{$script_url}' async></script>"; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

		// Append the script tag just before the closing body tag.
		return str_replace( '</body>', $inline_script . $script_tag . '</body>', $html );
	}
}
