<?php
namespace WP_Rocket\Optimization;

use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Optimization\Abstract_Optimization;

/**
 * Use the CDN for the favicon and "touch" icons.
 *
 * @since  3.2
 * @author Grégory Viguier
 */
class CDN_Favicons extends Abstract_Optimization {

	/**
	 * Plugin options instance.
	 *
	 * @var    Options
	 * @since  3.2
	 * @author Grégory Viguier
	 */
	protected $options;

	/**
	 * Files excluded from CDN, ready to be used as regex pattern.
	 * Pattern deleimiter is `#`.
	 *
	 * @var    string
	 * @since  3.2
	 * @author Grégory Viguier
	 */
	protected $excluded_files_regex;

	/**
	 * CNAMES for the related zones.
	 *
	 * @var    array
	 * @since  3.2
	 * @author Grégory Viguier
	 */
	protected $cnames;

	/**
	 * CNAMES for the related zones, ready to be used as regex pattern.
	 * Pattern deleimiter is `/`.
	 *
	 * @var    string
	 * @since  3.2
	 * @author Grégory Viguier
	 */
	protected $cnames_regex;

	/**
	 * Constructor.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @param Options $options Plugin options instance.
	 */
	public function __construct( Options $options ) {
		$this->options = $options;
	}

	/**
	 * Find favicons and replace their URL.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @param  string $html HTML content.
	 * @return string
	 */
	public function add_cdn( $html ) {
		$html_nocomments = $this->hide_comments( $html );
		/**
		 * The following pattern will match these tags:
		 *     <link rel="icon" href="https://example.com/favicon.png" />
		 *     <link rel="shortcut icon" href="https://example.com/favicon.png" />
		 *     <link rel="icon shortcut" href="https://example.com/favicon.png" />
		 *     <link rel="apple-touch-icon" href="https://example.com/apple-touch-icon.png" />
		 *     <link rel="apple-touch-icon-precomposed" href="https://example.com/apple-touch-icon.png" />
		 *     <link rel="mask-icon" href="https://example.com/safari-pinned-tab.svg" />
		 *     <meta name="msapplication-TileImage" content="https://example.com/mstile.png" />
		 * Tags with attribute values without quotes are not matched.
		 */
		$favicon_tags = $this->find( '<(?:link|meta)\s(?:[^>]+[\s"\'])?(?:rel|name)\s*=\s*["\']\s*(?:icon|shortcut\s+icon|icon\s+shortcut|apple-touch-icon(?:-precomposed)?|mask-icon|msapplication-TileImage)\s*["\'][^>]*>', $html_nocomments );

		if ( ! $favicon_tags ) {
			return $html;
		}

		foreach ( $favicon_tags as $favicon_tag ) {
			if ( ! preg_match( '@[\s\'"](?:href|content)\s*=\s*["\']\s*(?<url>[^"\']+)\s*["\']@i', $favicon_tag[0], $matches ) ) {
				continue;
			}

			if ( ! $this->can_replace( $matches['url'] ) ) {
				continue;
			}

			$url = $this->get_cdn_url( $matches['url'] );

			if ( ! $url ) {
				continue;
			}

			$replace_tag = str_replace( $matches['url'], $url, $matches[0] );
			$html        = str_replace( $matches[0], $replace_tag, $html );
		}

		return $html;
	}

	/**
	 * Determine if we can use the CDN.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @return boolean
	 */
	public function is_allowed() {
		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
		}

		if ( ! $this->options->get( 'cdn' ) ) {
			return false;
		}

		return (bool) $this->get_cnames();
	}

	/**
	 * Determine if we can apply the CDN on a URL.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @param  string $url source URL.
	 * @return bool
	 */
	protected function can_replace( $url ) {
		if ( $this->is_external_file( $url ) ) {
			return false;
		}

		if ( $this->is_excluded( $url ) ) {
			return false;
		}

		return ! $this->has_cdn_already( $url );
	}

	/**
	 * Determine if a URL is excluded.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @param string $url source URL.
	 * @return bool
	 */
	protected function is_excluded( $url ) {
		if ( ! is_string( $this->excluded_files_regex ) ) {
			// Cache the excluded files.
			$this->excluded_files_regex = \get_rocket_cdn_reject_files();

			if ( ! $this->excluded_files_regex ) {
				$this->excluded_files_regex = '';
				return false;
			}

			$this->excluded_files_regex = explode( '|', $this->excluded_files_regex );
			$this->excluded_files_regex = array_map( 'trim', $this->excluded_files_regex );
			$this->excluded_files_regex = array_filter( $this->excluded_files_regex );

			if ( ! $this->excluded_files_regex ) {
				$this->excluded_files_regex = '';
				return false;
			}

			foreach ( $this->excluded_files_regex as $i => $excluded_file ) {
				// Escape character for future use in regex pattern.
				$this->excluded_files_regex[ $i ] = str_replace( '#', '\#', $excluded_file );
			}

			$this->excluded_files_regex = implode( '|', $this->excluded_files_regex );
		}

		if ( $this->excluded_files_regex && preg_match( '#^' . $this->excluded_files_regex . '$#', \rocket_clean_exclude_file( $url ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the CDN has already been applied to a URL.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @param  string $url source URL.
	 * @return bool
	 */
	protected function has_cdn_already( $url ) {
		if ( is_string( $this->cnames_regex ) ) {
			if ( '' === $this->cnames_regex ) {
				return false;
			}

			return preg_match( '@^(https?:)?//(' . $this->cnames_regex . ')/@i', $url );
		}

		$this->cnames_regex = $this->get_cnames();

		if ( ! $this->cnames_regex ) {
			$this->cnames_regex = '';
			return false;
		}

		foreach ( $this->cnames_regex as $i => $cname ) {
			$cname = preg_replace( '@^(https?:)?//@i', '', $cname );
			$cname = preg_replace( '@/+@', '/', $cname );
			$cname = rtrim( $cname, '/' );

			if ( '' !== $cname ) {
				$this->cnames_regex[ $i ] = preg_quote( $this->cnames_regex[ $i ], '@' );
			} else {
				unset( $this->cnames_regex[ $i ] );
			}
		}

		$this->cnames_regex = implode( '|', $this->cnames_regex );

		if ( '' === $this->cnames_regex ) {
			return false;
		}

		return preg_match( '@^(https?:)?//(' . $this->cnames_regex . ')/@i', $url );
	}

	/**
	 * Gets the CDN zones.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public function get_zones() {
		return [ 'all', 'images' ];
	}

	/**
	 * Get the CNAMES for the related zones.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	protected function get_cnames() {
		if ( ! is_array( $this->cnames ) ) {
			$this->cnames = \get_rocket_cdn_cnames( $this->get_zones() );
		}

		return $this->cnames;
	}

	/**
	 * Change a local URL into a CDN URL.
	 *
	 * @since  3.2
	 * @author Grégory Viguier
	 *
	 * @param  string $url Source URL.
	 * @return string
	 */
	protected function get_cdn_url( $url ) {
		$cdn_url = \get_rocket_cdn_url( $url, $this->get_zones() );

		/**
		 * Filter image file URL with CDN hostname.
		 *
		 * @since  3.2
		 * @author Grégory Viguier
		 *
		 * @param string $cdn_url The new URL.
		 * @param string $url     The original URL.
		 */
		return apply_filters( 'rocket_image_url', $cdn_url, $url );
	}
}
