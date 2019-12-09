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
		$file       = get_rocket_parse_url( $url );
		$wp_content = get_rocket_parse_url( WP_CONTENT_URL );
		// This filter is documented in inc/classes/admin/settings/class-settings.php.
		$hosts   = apply_filters( 'rocket_cdn_hosts', [], $this->get_zones() );
		$hosts[] = $wp_content['host'];
		$langs   = get_rocket_i18n_uri();

		// Get host for all langs.
		if ( $langs ) {
			foreach ( $langs as $lang ) {
				$url_host = rocket_extract_url_component( $lang, PHP_URL_HOST );
				if ( ! empty( $url_host ) ) {
					$hosts[] = $url_host;
				}
			}
		}

		$hosts_index = array_flip( array_unique( $hosts ) );

		// URL has domain and domain is not part of the internal domains.
		if ( isset( $file['host'] ) && ! empty( $file['host'] ) && ! isset( $hosts_index[ $file['host'] ] ) ) {
			return true;
		}

		// URL has no domain and doesn't contain the WP_CONTENT path or wp-includes.
		if ( ! isset( $file['host'] ) && ! preg_match( '#(' . $wp_content['path'] . '|wp-includes)#', $file['path'] ) ) {
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
		// This filter is documented in inc/classes/admin/settings/class-settings.php.
		$hosts         = apply_filters( 'rocket_cdn_hosts', [], $this->get_zones() );
		$hosts['home'] = rocket_extract_url_component( home_url(), PHP_URL_HOST );
		$hosts_index   = array_flip( $hosts );

		return rocket_url_to_path( strtok( $url, '?' ), $hosts_index );
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
