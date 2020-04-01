<?php
namespace WP_Rocket\Optimization;

/**
 * Base abstract class for files optimization
 *
 * @since 3.1
 * @author Remy Perona
 */
abstract class Abstract_Optimization {
	/**
	 * Finds nodes matching the pattern in the HTML
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
	 * Determines if the file is external
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

		/**
		 * Filters the allowed hosts for optimization
		 *
		 * @since  3.4
		 * @author Remy Perona
		 *
		 * @param array $hosts Allowed hosts.
		 * @param array $zones Zones to check available hosts.
		 */
		$hosts      = apply_filters( 'rocket_cdn_hosts', [], $this->get_zones() );
		$wp_content = get_rocket_parse_url( content_url() );
		$hosts[]    = $wp_content['host'];
		$langs      = get_rocket_i18n_uri();

		// Get host for all langs.
		if ( ! empty( $langs ) ) {
			foreach ( $langs as $lang ) {
				$url_host = wp_parse_url( $lang, PHP_URL_HOST );

				if ( ! isset( $url_host ) ) {
					continue;
				}

				$hosts[] = $url_host;
			}
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
		if ( ! preg_match( '#(' . $wp_content['path'] . '|wp-includes)#', $file['path'] ) ) {
			return true;
		}

		return false;
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

		return rocket_put_content( $file, $content );
	}

	/**
	 * Gets the file path from an URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url File URL.
	 * @return string
	 */
	protected function get_file_path( $url ) {
		$url            = strtok( $url, '?' );
		$wp_content_dir = apply_filters( 'rocket_wp_content_dir', rocket_get_constant( 'WP_CONTENT_DIR' ) );
		$root_dir       = trailingslashit( dirname( $wp_content_dir ) );
		$root_url       = str_replace( wp_basename( $wp_content_dir ), '', content_url() );
		$url_host       = wp_parse_url( $url, PHP_URL_HOST );

		// relative path.
		if ( null === $url_host ) {
			$subdir_levels = substr_count( preg_replace( '/https?:\/\//', '', site_url() ), '/' );
			$url           = trailingslashit( site_url() . str_repeat( '/..', $subdir_levels ) ) . ltrim( $url, '/' );
		}

		/**
		 * Filters the URL before converting it to a path
		 *
		 * @since 3.5.3
		 * @author Remy Perona
		 *
		 * @param string $url   URL of the asset.
		 * @param array  $zones CDN zones corresponding to the current assets type.
		 */
		$url = apply_filters( 'rocket_asset_url', $url, $this->get_zones() );

		$root_url = preg_replace( '/^https?:/', '', $root_url );
		$url      = preg_replace( '/^https?:/', '', $url );
		$file     = str_replace( $root_url, $root_dir, $url );
		$file     = rocket_realpath( $file );

		/**
		 * Filters the absolute path to the asset file
		 *
		 * @since 3.3
		 * @author Remy Perona
		 *
		 * @param string $file Absolute path to the file.
		 * @param string $url  URL of the asset.
		 */
		$file = apply_filters( 'rocket_url_to_path', $file, $url );

		if ( ! rocket_direct_filesystem()->is_readable( $file ) ) {
			return false;
		}

		return $file;
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
