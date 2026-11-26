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
		return $this->normalize_url( $url, false );
	}

	/**
	 * Aligns the URL scheme and host with home_url()'s when the URL belongs to the same site.
	 *
	 * @param string $url URL to normalize.
	 *
	 * @return string
	 */
	protected function normalize_url_scheme_and_host( string $url ): string {
		return $this->normalize_url( $url, true );
	}

	/**
	 * Aligns the URL scheme, and optionally the host, with home_url()'s when the URL belongs to the same site.
	 *
	 * @param string $url       URL to normalize.
	 * @param bool   $with_host Whether to also replace the host with home_url()'s.
	 *
	 * @return string
	 */
	private function normalize_url( string $url, bool $with_host ): string {
		$home_url  = home_url();
		$home_host = (string) wp_parse_url( $home_url, PHP_URL_HOST );
		$url_host  = (string) wp_parse_url( $url, PHP_URL_HOST );

		if ( '' === $url_host || '' === $home_host ) {
			return $url;
		}

		$strip_www = static function ( string $host ): string {
			return preg_replace( '/^www\./i', '', $host ) ?? $host;
		};

		if ( $strip_www( $url_host ) !== $strip_www( $home_host ) ) {
			return $url;
		}

		$home_scheme = (string) wp_parse_url( $home_url, PHP_URL_SCHEME );

		if ( '' !== $home_scheme ) {
			$url = set_url_scheme( $url, $home_scheme );
		}

		if ( $with_host && $url_host !== $home_host ) {
			$url = str_replace( '://' . $url_host, '://' . $home_host, $url );
		}

		return $url;
	}
}
