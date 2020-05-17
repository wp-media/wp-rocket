<?php
namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\Admin\Options_Data;

/**
 * Base abstract class for files optimization
 *
 * @since 3.1
 * @author Remy Perona
 */
abstract class AbstractOptimization {

	/**
	 * Plugin options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Minify key.
	 *
	 * @var mixed
	 */
	protected $minify_key;

	/**
	 * Concatenated list of excluded files.
	 *
	 * @var string
	 */
	protected $excluded_files;

	/**
	 * Minify base path.
	 *
	 * @var string
	 */
	protected $minify_base_path;

	/**
	 * Minify base URL.
	 *
	 * @var string
	 */
	protected $minify_base_url;

	/**
	 * Initializes the minify base path and URL.
	 */
	protected function init_base_path_and_url() {
		$site_id                = get_current_blog_id() . '/';
		$this->minify_base_path = rocket_get_constant( 'WP_ROCKET_MINIFY_CACHE_PATH' ) . $site_id;
		$this->minify_base_url  = rocket_get_constant( 'WP_ROCKET_MINIFY_CACHE_URL' ) . $site_id;
	}

	/**
	 * Finds nodes matching the pattern in the HTML.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $pattern Pattern to match.
	 * @param string $html HTML content.
	 * @return bool|array
	 */
	protected function find( $pattern, $html ) {
		preg_match_all( '/' . $pattern . '/Umsi', $html, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return false;
		}

		return $matches;
	}

	/**
	 * Determines if the file is external.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string $url URL of the file.
	 * @return bool True if external, false otherwise
	 */
	protected function is_external_file( $url ) {
		$file = get_rocket_parse_url( $url );

		if ( empty( $file['path'] ) ) {
			return true;
		}

		$wp_content = wp_parse_url( content_url() );

		if ( empty( $wp_content['host'] ) || empty( $wp_content['path'] ) ) {
			return true;
		}

		/**
		 * Filters the allowed hosts for optimization
		 *
		 * @since  3.4
		 * @author Remy Perona
		 *
		 * @param array $hosts Allowed hosts.
		 * @param array $zones Zones to check available hosts.
		 */
		$hosts   = (array) apply_filters( 'rocket_cdn_hosts', [], $this->get_zones() );
		$hosts[] = $wp_content['host'];
		$langs   = get_rocket_i18n_uri();

		// Get host for all langs.
		foreach ( $langs as $lang ) {
			$url_host = wp_parse_url( $lang, PHP_URL_HOST );

			if ( ! isset( $url_host ) ) {
				continue;
			}

			$hosts[] = $url_host;
		}

		$hosts = array_unique( $hosts );

		if ( empty( $hosts ) ) {
			return true;
		}

		// URL has domain and domain is part of the internal domains.
		if ( ! empty( $file['host'] ) ) {
			foreach ( $hosts as $host ) {
				if ( false !== strpos( $url, $host ) ) {
					return false;
				}
			}

			return true;
		}

		// URL has no domain and doesn't contain the WP_CONTENT path or wp-includes.
		return ! preg_match( '#(' . $wp_content['path'] . '|wp-includes)#', $file['path'] );
	}

	/**
	 * Writes the content to a file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $content       Content to write.
	 * @param string $file          Path to the file to write in.
	 * @return bool
	 */
	protected function write_file( $content, $file ) {
		if ( rocket_direct_filesystem()->is_readable( $file ) ) {
			return true;
		}

		if ( ! rocket_mkdir_p( dirname( $file ) ) ) {
			return false;
		}

		if ( function_exists( 'gzencode' ) ) {
			// This filter is documented in inc/classes/Buffer/class-cache.php.
			$gzip_content = gzencode( $content, apply_filters( 'rocket_gzencode_level_compression', 6 ) );

			if ( $gzip_content ) {
				rocket_put_content( $file . '.gz', $gzip_content );
			}
		}

		return rocket_put_content( $file, $content );
	}

	/**
	 * Gets the file path from an URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url File URL.
	 * @return bool|string
	 */
	protected function get_file_path( $url ) {
		return rocket_url_to_path( strtok( $url, '?' ), $this->get_zones() );
	}

	/**
	 * Gets content of a file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $file File path.
	 * @return string
	 */
	protected function get_file_content( $file ) {
		return rocket_direct_filesystem()->get_contents( $file );
	}

	/**
	 * Hides unwanted blocks from the HTML to be parsed for optimization
	 *
	 * @since 3.1.4
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	protected function hide_comments( $html ) {
		$html = preg_replace( '#<!--\s*noptimize\s*-->.*?<!--\s*/\s*noptimize\s*-->#is', '', $html );
		$html = preg_replace( '/<!--(.*)-->/Uis', '', $html );

		return $html;
	}
}
