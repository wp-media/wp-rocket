<?php
namespace WP_Rocket\Optimization;

use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Optimization\Abstract_Optimization;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Remove query string from static resources
 *
 * @since 3.1
 * @author Remy Perona
 */
class Remove_Query_String extends Abstract_Optimization {
	use \WP_Rocket\Optimization\CSS\Path_Rewriter;

	/**
	 * Plugin options instance.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Cache busting base path
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $busting_path;

	/**
	 * Cache busting base URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $busting_url;

	/**
	 * Excluded files from optimization
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $excluded_files;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Options $options      Plugin options instance.
	 * @param string  $busting_path Base cache busting files path.
	 * @param string  $busting_url  Base cache busting files URL.
	 */
	public function __construct( Options $options, $busting_path, $busting_url ) {
		$this->options      = $options;
		$this->busting_path = $busting_path . get_current_blog_id() . '/';
		$this->busting_url  = $busting_url . get_current_blog_id() . '/';

		/**
		 * Filters files to exclude from cache busting
		 *
		 * @since 2.9.3
		 * @author Remy Perona
		 *
		 * @param array $excluded_files An array of filepath to exclude.
		 */
		$this->excluded_files = apply_filters( 'rocket_exclude_cache_busting', array() );
		$delimiter            = array_fill( 0, count( $this->excluded_files ), '#' );
		$this->excluded_files = array_map( 'preg_quote', $this->excluded_files, $delimiter );
		$this->excluded_files = implode( '|', $this->excluded_files );
	}

	/**
	 * Set DOM crawler from provided HTML content
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param HtmlPageCrawler $crawler Crawler instance.
	 * @param string          $html   HTML content.
	 * @return void
	 */
	public function set_crawler( HtmlPageCrawler $crawler, $html ) {
		$this->crawler = $crawler::create( $html );
	}

	/**
	 * Remove query strings for CSS/JS files that have one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function optimize() {
		$style_nodes  = $this->find( 'link[href*=".css"]' );
		$script_nodes = $this->find( 'script[src*=".js"]' );

		if ( ! $style_nodes && ! $script_nodes ) {
			return $this->crawler->saveHTML();
		}

		if ( $style_nodes ) {
			$style_nodes->each( function( \Wa72\HtmlPageDom\HtmlPageCrawler $node, $i ) {
				$url = $node->attr( 'href' );

				$url = $this->can_replace( $url );

				if ( ! $url ) {
					return;
				}

				$optimized_url = $this->replace_url( $url, 'css' );

				if ( ! $optimized_url ) {
					return;
				}

				$node->attr( 'href', $optimized_url );
			} );
		}

		if ( $script_nodes ) {
			$script_nodes->each( function( \Wa72\HtmlPageDom\HtmlPageCrawler $node, $i ) {
				$url = $node->attr( 'src' );

				$url = $this->can_replace( $url );

				if ( ! $url ) {
					return;
				}

				$optimized_url = $this->replace_url( $url, 'js' );

				if ( ! $optimized_url ) {
					return;
				}

				$node->attr( 'src', $optimized_url );
			} );
		}

		return $this->crawler->saveHTML();
	}

	/**
	 * Gets the CDN zones.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public function get_zones() {
		return [ 'all', 'css_and_js', 'css', 'js' ];
	}

	/**
	 * Determines if we can optimize
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	public function is_allowed() {
		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
		}

		if ( ! $this->options->get( 'remove_query_strings' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if we can perform the remove query string on that URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url source URL.
	 * @return bool\string
	 */
	protected function can_replace( $url ) {
		$parsed_url = get_rocket_parse_url( $url );

		if ( empty( $parsed_url['query'] ) ) {
			return false;
		}

		if ( false !== strpos( $url, 'ver=' . $GLOBALS['wp_version'] ) ) {
			$url = rtrim( str_replace( array( 'ver=' . $GLOBALS['wp_version'], '?&', '&&' ), array( '', '?', '&' ), $url ), '?&' );
		}

		if ( $this->is_external_file( $url ) ) {
			return false;
		}

		if ( $this->is_excluded( $url ) ) {
			return false;
		}

		return $url;
	}

	/**
	 * Determines if the URL is excluded
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url source URL.
	 * @return bool
	 */
	protected function is_excluded( $url ) {
		if ( preg_match( '#^' . $this->excluded_files . '$#', rocket_clean_exclude_file( $url ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Replaces the original URL with the cache busting URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url       source URL.
	 * @param string $extension file extension.
	 * @return bool|string
	 */
	protected function replace_url( $url, $extension ) {
		$parsed_url = get_rocket_parse_url( $url );

		if ( empty( $parsed_url['query'] ) ) {
			return $url;
		}

		$relative_src = ltrim( $parsed_url['path'] . '?' . $parsed_url['query'], '/' );
		$filename     = preg_replace( '/\.(' . $extension . ')\?(?:timestamp|ver)=([^&]+)(?:.*)/', '-$2.$1', $relative_src );

		$busting_file = $this->busting_path . $filename;

		if ( ! rocket_direct_filesystem()->is_readable( $busting_file ) ) {
			$file            = $this->get_file_path( $url );
			$busting_content = $this->get_file_content( $file );

			if ( ! $busting_content ) {
				return false;
			}

			if ( 'css' === $extension ) {
				$busting_content = $this->rewrite_paths( $file, $busting_content );
			}

			if ( ! $this->write_file( $busting_content, $busting_file ) ) {
				return false;
			}
		}

		return $this->get_busting_url( $filename, $extension );
	}

	/**
	 * Gets the cache busting URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $filename  Cache busting filename.
	 * @param string $extension File extension.
	 * @return string
	 */
	protected function get_busting_url( $filename, $extension ) {
		$zones = [ 'all', 'css_and_js', $extension ];
		$url   = get_rocket_cdn_url( $this->busting_url . $filename, $zones );

		switch ( $extension ) {
			case 'css':
				// This filter is documented in inc/classes/optimization/css/class-abstract-css-optimization.php.
				$url = apply_filters( 'rocket_css_url', $url );
				break;
			case 'js':
				// This filter is documented in inc/classes/optimization/css/class-abstract-js-optimization.php.
				$url = apply_filters( 'rocket_js_url', $url );
				break;
		}

		return $url;
	}
}
