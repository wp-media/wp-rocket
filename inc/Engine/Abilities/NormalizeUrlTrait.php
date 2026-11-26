<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

trait NormalizeUrlTrait {
	/**
	 * Aligns the URL scheme with home_url()'s when the URL belongs to the same site (www-insensitive).
	 *
	 * @param string $url URL to normalize.
	 *
	 * @return string
	 */
	protected function normalize_url_scheme( string $url ): string {
		$home_scheme = (string) wp_parse_url( home_url(), PHP_URL_SCHEME );

		if ( '' === $home_scheme || ! $this->is_same_site_host( $url ) ) {
			return $url;
		}

		return set_url_scheme( $url, $home_scheme );
	}

	/**
	 * Aligns the URL scheme and host with home_url()'s when the URL belongs to the same site.
	 *
	 * @param string $url URL to normalize.
	 *
	 * @return string
	 */
	protected function normalize_url_scheme_and_host( string $url ): string {
		$url_host  = (string) wp_parse_url( $url, PHP_URL_HOST );
		$home_host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

		if ( ! $this->is_same_site_host( $url ) ) {
			return $url;
		}

		$url = $this->normalize_url_scheme( $url );

		if ( $url_host !== $home_host ) {
			$url = str_replace( '://' . $url_host, '://' . $home_host, $url );
		}

		return $url;
	}

	/**
	 * Checks whether the URL host matches the home_url() host, ignoring a leading www.
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool
	 */
	private function is_same_site_host( string $url ): bool {
		$url_host  = (string) wp_parse_url( $url, PHP_URL_HOST );
		$home_host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

		if ( '' === $url_host || '' === $home_host ) {
			return false;
		}

		$strip_www = static function ( string $host ): string {
			return preg_replace( '/^www\./i', '', $host ) ?? $host;
		};

		return $strip_www( $url_host ) === $strip_www( $home_host );
	}
}
