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

		if ( ! empty( $file['query'] ) ) {
			return true;
		}

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
		return rocket_url_to_path( strtok( $url, '?' ), $this->get_zones() );
	}

	/**
	 * Gets content of a file
	 *
	 * @param string $file File path.
	 * @return string
	 */
	protected function get_file_content( $file ) {
		return rocket_direct_filesystem()->get_contents( $file );
	}

}
