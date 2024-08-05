<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Frontend;

use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Engine\Optimization\UrlTrait;

class Controller {
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
	private function generate_lcp_link_tag_with_sources( $lcp ): array {
		$pairs = [
			'tags'    => '',
			'sources' => [],
		];

		if ( ! $lcp && ! is_object( $lcp ) ) {
			return $pairs;
		}

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

		$default_width_threshold  = $is_mobile ? 393 : 1600;
		$default_height_threshold = $is_mobile ? 830 : 700;
		/**
		 * Filters the width threshold for the LCP beacon.
		 *
		 * @param int    $width_threshold The width threshold. Default is 393 for mobile and 1920 for others.
		 * @param bool   $is_mobile       True if the current device is mobile, false otherwise.
		 * @param string $url             The current URL.
		 *
		 * @return int The filtered width threshold.
		 */
		$width_threshold = apply_filters( 'rocket_lcp_width_threshold', $default_width_threshold, $is_mobile, $url );

		/**
		 * Filters the height threshold for the LCP beacon.
		 *
		 * @param int    $height_threshold The height threshold. Default is 830 for mobile and 1080 for others.
		 * @param bool   $is_mobile        True if the current device is mobile, false otherwise.
		 * @param string $url              The current URL.
		 *
		 * @return int The filtered height threshold.
		 */
		$height_threshold = apply_filters( 'rocket_lcp_height_threshold', $default_height_threshold, $is_mobile, $url );

		if ( ! is_int( $width_threshold ) ) {
			$width_threshold = $default_width_threshold;
		}

		if ( ! is_int( $height_threshold ) ) {
			$height_threshold = $default_height_threshold;
		}

		$default_delay = 500;

		/**
		 * Filters the delay before the LCP beacon is triggered.
		 *
		 * @param int $delay The delay in milliseconds. Default is 500.
		 */
		$delay = apply_filters( 'rocket_lcp_delay', $default_delay );

		if ( ! is_int( $delay ) ) {
			$delay = $default_delay;
		}

		$data = [
			'ajax_url'         => admin_url( 'admin-ajax.php' ),
			'nonce'            => wp_create_nonce( 'rocket_lcp' ),
			'url'              => $url,
			'is_mobile'        => $is_mobile,
			'elements'         => $this->lcp_atf_elements(),
			'width_threshold'  => $width_threshold,
			'height_threshold' => $height_threshold,
			'delay'            => $delay,
			'debug'            => rocket_get_constant( 'WP_ROCKET_DEBUG' ),
		];

		$inline_script = '<script>var rocket_lcp_data = ' . wp_json_encode( $data ) . '</script>';

		// Get the URL of the script.
		$script_url = rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'lcp-beacon' . $min . '.js';

		// Create the script tag.
		$script_tag = "<script data-name=\"wpr-lcp-beacon\" src='{$script_url}' async></script>"; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

		// Append the script tag just before the closing body tag.
		return str_replace( '</body>', $inline_script . $script_tag . '</body>', $html );
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
	 * Returns a comma-separated list of elements to be considered for the lcp/above-the-fold optimization.
	 *
	 * @return string
	 */
	private function lcp_atf_elements(): string {
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
		$elements = apply_filters( 'rocket_atf_elements', $elements );

		if ( ! is_array( $elements ) ) {
			$elements = $default_elements;
		}

		$elements = array_filter( $elements, 'is_string' );

		return implode( ', ', $elements );
	}
}
