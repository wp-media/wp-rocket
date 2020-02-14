<?php
namespace WP_Rocket\Optimization\CSS;

use WP_Rocket\Optimization\Abstract_Optimization;
use WP_Rocket\Admin\Options_Data as Options;

/**
 * Abstract class for CSS Optimization
 *
 * @since 3.1
 * @author Remy Perona
 */
abstract class Abstract_CSS_Optimization extends Abstract_Optimization {
	const FILE_TYPE = 'css';

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Options $options Options instance.
	 */
	public function __construct( Options $options ) {
		$this->options          = $options;
		$this->minify_key       = $this->options->get( 'minify_css_key', create_rocket_uniqid() );
		$this->excluded_files   = $this->get_excluded_files();
		$this->minify_base_path = rocket_get_constant( 'WP_ROCKET_MINIFY_CACHE_PATH' ) . get_current_blog_id() . '/';
		$this->minify_base_url  = rocket_get_constant( 'WP_ROCKET_MINIFY_CACHE_URL' ) . get_current_blog_id() . '/';
	}

	/**
	 * Get all files to exclude from minification/concatenation.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @return string
	 */
	protected function get_excluded_files() {
		$excluded_files = $this->options->get( 'exclude_css', [] );

		/**
		 * Filters CSS files to exclude from minification/concatenation.
		 *
		 * @since 2.6
		 *
		 * @param array $excluded_files List of excluded CSS files.
		*/
		$excluded_files = apply_filters( 'rocket_exclude_css', $excluded_files );

		if ( empty( $excluded_files ) ) {
			return '';
		}

		foreach ( $excluded_files as $i => $excluded_file ) {
			$excluded_files[ $i ] = str_replace( '#', '\#', $excluded_file );
		}

		return implode( '|', $excluded_files );
	}

	/**
	 * Returns the CDN zones.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public function get_zones() {
		return [ 'all', 'css_and_js', self::FILE_TYPE ];
	}

	/**
	 * Gets the minify URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $filename Minified filename.
	 * @param string $original_url Original URL for this file. Optional.
	 * @return string
	 */
	protected function get_minify_url( $filename, $original_url = '' ) {
		$minify_url = $this->minify_base_url . $filename;

		/**
		 * Filters CSS file URL with CDN hostname
		 *
		 * @since 2.1
		 *
		 * @param string $minify_url Minified file URL.
		 * @param string $original_url Original URL for this file.
		 */
		return apply_filters( 'rocket_css_url', $minify_url, $original_url );
	}

	/**
	 * Determines if it is a file excluded from minification
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param Array $tag Tag corresponding to a CSS file.
	 * @return bool True if it is a file excluded, false otherwise
	 */
	protected function is_minify_excluded_file( $tag ) {
		// File should not be minified.
		if ( false !== strpos( $tag[0], 'data-minify=' ) || false !== strpos( $tag[0], 'data-no-minify=' ) ) {
			return true;
		}

		if ( false !== strpos( $tag[0], 'media=' ) && ! preg_match( '/media=["\'](?:\s*|[^"\']*?\b(all|screen)\b[^"\']*?)["\']/i', $tag[0] ) ) {
			return true;
		}

		if ( false !== strpos( $tag[0], 'only screen and' ) ) {
			return true;
		}

		$file_path = rocket_extract_url_component( $tag[2], PHP_URL_PATH );

		// File extension is not css.
		if ( pathinfo( $file_path, PATHINFO_EXTENSION ) !== self::FILE_TYPE ) {
			return true;
		}

		if ( ! empty( $this->excluded_files ) ) {
			// File is excluded from minification/concatenation.
			if ( preg_match( '#^(' . $this->excluded_files . ')$#', $file_path ) ) {
				return true;
			}
		}

		return false;
	}
}
