<?php
namespace WP_Rocket\Optimization\JS;

use WP_Rocket\Optimization\Abstract_Optimization;
use WP_Rocket\Admin\Options_Data as Options;

/**
 * Abstract class for JS optimization
 *
 * @since 3.1
 * @author Remy Perona
 */
class Abstract_JS_Optimization extends Abstract_Optimization {
	const FILE_TYPE = 'js';

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
		$this->minify_key       = $this->options->get( 'minify_js_key', create_rocket_uniqid() );
		$this->excluded_files   = $this->get_excluded_files();
		$this->minify_base_path = WP_ROCKET_MINIFY_CACHE_PATH . get_current_blog_id() . '/';
		$this->minify_base_url  = WP_ROCKET_MINIFY_CACHE_URL . get_current_blog_id() . '/';
	}

	/**
	 * Get all files to exclude from minification/concatenation.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @return string A list of files to exclude, ready to be used in a regex pattern.
	 */
	protected function get_excluded_files() {
		$excluded_files = $this->options->get( 'exclude_js', [] );
		$jquery_url     = $this->get_jquery_url();

		if ( $jquery_url ) {
			$excluded_files[] = $jquery_url;
		}

		/**
		 * Filter JS files to exclude from minification/concatenation.
		 *
		 * @since 2.6
		 *
		 * @param array $js_files List of excluded JS files.
		*/
		$excluded_files = apply_filters( 'rocket_exclude_js', $excluded_files );

		if ( empty( $excluded_files ) ) {
			return '';
		}

		foreach ( $excluded_files as $i => $excluded_file ) {
			// Escape characters for future use in regex pattern.
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
	 * Determines if it is a file excluded from minification
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param Array $tag Tag corresponding to a JS file.
	 * @return bool True if it is a file excluded, false otherwise
	 */
	protected function is_minify_excluded_file( $tag ) {
		// File should not be minified.
		if ( false !== strpos( $tag[0], 'data-minify=' ) || false !== strpos( $tag[0], 'data-no-minify=' ) ) {
			return true;
		}

		$file_path = rocket_extract_url_component( $tag[2], PHP_URL_PATH );

		// File extension is not js.
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

	/**
	 * Gets the minify URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $filename Minified filename.
	 * @return string
	 */
	protected function get_minify_url( $filename ) {
		$minify_url = get_rocket_cdn_url( $this->minify_base_url . $filename, $this->get_zones() );

		/**
		 * Filters JS file URL with CDN hostname
		 *
		 * @since 2.1
		 *
		 * @param string $minify_url Minified file URL.
		*/
		return apply_filters( 'rocket_js_url', $minify_url );
	}

	/**
	 * Gets jQuery URL if defer JS safe mode is active
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return bool\string
	 */
	protected function get_jquery_url() {
		global $wp_scripts;

		if ( ! $this->options->get( 'defer_all_js', 0 ) || ! $this->options->get( 'defer_all_js_safe', 0 ) ) {
			return false;
		}

		if ( ! isset( $wp_scripts->registered['jquery-core']->src ) ) {
			return false;
		}

		if ( '' === wp_parse_url( $wp_scripts->registered['jquery-core']->src, PHP_URL_HOST ) ) {
			return rocket_clean_exclude_file( site_url( $wp_scripts->registered['jquery-core']->src ) );
		}

		return $wp_scripts->registered['jquery-core']->src;
	}
}
