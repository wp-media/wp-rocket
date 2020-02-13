<?php
namespace WP_Rocket\Optimization;

use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Optimization\Abstract_Optimization;

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
	}

	/**
	 * Returns a regex-ready string with the excluded filepaths for the Remove Query Strings option
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return string
	 */
	protected function get_excluded_files() {
		static $excluded_files;

		if ( isset( $excluded_files ) ) {
			return $excluded_files;
		}

		/**
		 * Filters files to exclude from cache busting
		 *
		 * @since 2.9.3
		 * @author Remy Perona
		 *
		 * @param array $excluded_files An array of filepath to exclude.
		 */
		$excluded_files = apply_filters( 'rocket_exclude_cache_busting', [] );

		if ( empty( $excluded_files ) ) {
			$excluded_files = '';

			return $excluded_files;
		}

		foreach ( $excluded_files as $i => $excluded_file ) {
			// Escape character for future use in regex pattern.
			$excluded_files[ $i ] = str_replace( '#', '\#', $excluded_file );
		}

		$excluded_files = implode( '|', $excluded_files );

		return $excluded_files;
	}

	/**
	 * Remove query strings for CSS files that have one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function remove_query_strings_css( $html ) {
		$html_nocomments = preg_replace( '/<!--(.*)-->/Uis', '', $html );
		$styles          = $this->find( '<link\s+([^>]+[\s\'"])?href\s*=\s*[\'"]\s*?([^\'"]+\.css(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>', $html_nocomments );

		if ( ! $styles ) {
			return $html;
		}

		foreach ( $styles as $style ) {
			$url = $style[2];

			$url = $this->can_replace( $url );

			if ( ! $url ) {
				continue;
			}

			$optimized_url = $this->replace_url( $url, 'css' );

			if ( ! $optimized_url ) {
				continue;
			}

			$replace_style = str_replace( $style[2], $optimized_url, $style[0] );
			$html          = str_replace( $style[0], $replace_style, $html );
		}

		return $html;
	}

	/**
	 * Remove query strings for JS files that have one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function remove_query_strings_js( $html ) {
		$html_nocomments = $this->hide_comments( $html );
		$scripts         = $this->find( '<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?([^\'"]+\.js(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>', $html_nocomments );

		if ( ! $scripts ) {
			return $html;
		}

		foreach ( $scripts as $script ) {
			$url = $script[2];

			$url = $this->can_replace( $url );

			if ( ! $url ) {
				continue;
			}

			$optimized_url = $this->replace_url( $url, 'js' );

			if ( ! $optimized_url ) {
				continue;
			}

			$replace_script = str_replace( $script[2], $optimized_url, $script[0] );
			$html           = str_replace( $script[0], $replace_script, $html );
		}

		return $html;
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
			$url = rtrim( str_replace( [ 'ver=' . $GLOBALS['wp_version'], '?&', '&&' ], [ '', '?', '&' ], $url ), '?&' );
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
		$excluded_files = $this->get_excluded_files();

		if ( ! empty( $excluded_files ) && preg_match( '#^' . $excluded_files . '$#', rocket_clean_exclude_file( $url ) ) ) {
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

		if ( $relative_src === $filename ) {
			return $url;
		}

		$busting_file = $this->busting_path . $filename;
		$busting_url  = $this->get_busting_url( $filename, $extension, $url );

		if ( rocket_direct_filesystem()->is_readable( $busting_file ) ) {
			return $busting_url;
		}

		$file = $this->get_file_path( $url );

		if ( ! $file ) {
			return false;
		}

		$busting_content = $this->get_file_content( $file );

		if ( ! $busting_content ) {
			return false;
		}

		if ( 'css' === $extension ) {
			$busting_content = $this->rewrite_paths( $file, $busting_file, $busting_content );
		}

		if ( ! $this->write_file( $busting_content, $busting_file ) ) {
			return false;
		}

		return $busting_url;
	}

	/**
	 * Gets the cache busting URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $filename  Cache busting filename.
	 * @param string $extension File extension.
	 * @param string $original_url Original URL for the file.
	 * @return string
	 */
	protected function get_busting_url( $filename, $extension, $original_url ) {
		$url = $this->busting_url . $filename;

		switch ( $extension ) {
			case 'css':
				// This filter is documented in inc/classes/optimization/css/class-abstract-css-optimization.php.
				$url = apply_filters( 'rocket_css_url', $url, $original_url );
				break;
			case 'js':
				// This filter is documented in inc/classes/optimization/css/class-abstract-js-optimization.php.
				$url = apply_filters( 'rocket_js_url', $url, $original_url );
				break;
		}

		return $url;
	}
}
