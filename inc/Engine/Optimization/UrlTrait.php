<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization;

trait UrlTrait {

	/**
	 * Determines if the file is external.
	 *
	 * @since 3.8
	 *
	 * @param string $url URL of the file.
	 * @return bool True if external, false otherwise.
	 */
	private function is_external_file( $url ) {
		$file = get_rocket_parse_url( $url );

		if ( empty( $file['path'] ) ) {
			return true;
		}

		$parsed_site_url = wp_parse_url( site_url() );

		if ( empty( $parsed_site_url['host'] ) ) {
			return true;
		}

		/**
		 * Filters the allowed hosts for optimization
		 *
		 * @since  3.4
		 *
		 * @param array $hosts Allowed hosts.
		 * @param array $zones Zones to check available hosts.
		 */
		$hosts   = (array) apply_filters( 'rocket_cdn_hosts', [], [ 'all' ] );
		$hosts[] = $parsed_site_url['host'];
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

		return false;
	}

	/**
	 * Gets the file path from an URL
	 *
	 * @param string $url File URL.
	 * @return bool|string
	 */
	protected function get_file_path( $url ) {
		$url = $this->normalize_fullurl( $url );

		$path = rocket_url_to_path( $url );
		if ( $path ) {
			return $path;
		}

		$relative_url = ltrim( wp_make_link_relative( $url ), '/' );
		$ds           = rocket_get_constant( 'DIRECTORY_SEPARATOR' );
		$base_path    = isset( $_SERVER['DOCUMENT_ROOT'] ) ? ( sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) . $ds ) : '';

		return $base_path . str_replace( '/', $ds, $relative_url );
	}

	/**
	 * Normalize relative url to full url.
	 *
	 * @param string $url Url to be normalized.
	 * @param bool   $remove_query Remove Query string or not.
	 *
	 * @return string Normalized url.
	 */
	public function normalize_fullurl( string $url, bool $remove_query = true ) {
		$url        = htmlspecialchars_decode( $url );
		$parsed_url = wp_parse_url( $url );

		if ( $remove_query && ! empty( $parsed_url['query'] ) ) {
			$url = str_replace( '?' . $parsed_url['query'], '', $url );
		}

		if ( empty( $parsed_url['host'] ) ) {
			$relative_url        = ltrim( wp_make_link_relative( $url ), '/' );
			$site_url_components = wp_parse_url( site_url( '/' ) );
			return $site_url_components['scheme'] . '://' . $site_url_components['host'] . '/' . $relative_url;
		}

		return rocket_add_url_protocol( $url );
	}

	/**
	 * Gets content of a file
	 *
	 * @param string $file File path.
	 * @return string
	 */
	protected function get_file_content( $file ) {
		if ( empty( $file ) ) {
			return false;
		}
		return rocket_direct_filesystem()->get_contents( $file );
	}

}
