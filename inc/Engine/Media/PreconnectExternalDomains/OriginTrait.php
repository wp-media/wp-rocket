<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains;

trait OriginTrait {
	/**
	 * Returns the normalized origin (scheme://host[:port]) of a URL, like the browser URL.origin.
	 *
	 * Only http(s) URLs with a valid host and no path (other than "/"), query, fragment or user info are accepted.
	 *
	 * @param mixed $url URL to validate.
	 *
	 * @return string Normalized origin, or an empty string when the URL is not a valid origin.
	 */
	protected function get_origin( $url ): string {
		$parts = $this->get_origin_parts( $url );

		if ( empty( $parts ) ) {
			return '';
		}

		$scheme = strtolower( $parts['scheme'] );
		$host   = strtolower( $parts['host'] );

		if ( ! $this->is_valid_origin_host( $host ) || ! $this->is_canonical_authority( $url, $scheme, $host, $parts ) ) {
			return '';
		}

		$port = '';

		if ( isset( $parts['port'] ) && ( 'https' === $scheme ? 443 : 80 ) !== $parts['port'] ) {
			$port = ':' . $parts['port'];
		}

		return $scheme . '://' . $host . $port;
	}

	/**
	 * Parses a URL and keeps it only if it is shaped like an http(s) origin.
	 *
	 * @param mixed $url URL to parse.
	 *
	 * @return array URL parts, or an empty array when the URL is not shaped like an origin.
	 */
	private function get_origin_parts( $url ): array {
		if ( ! is_string( $url ) || '' === $url ) {
			return [];
		}

		// Checked before parsing: parse_url() rewrites control characters and its empty query/fragment handling differs across PHP versions.
		if ( preg_match( '/[\x00-\x20\x7f]/', $url ) || false !== strpbrk( $url, '?#@\\' ) ) {
			return [];
		}

		$parts = wp_parse_url( $url );

		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return [];
		}

		if ( isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] ) ) {
			return [];
		}

		if ( isset( $parts['path'] ) && '' !== $parts['path'] && '/' !== $parts['path'] ) {
			return [];
		}

		if ( ! in_array( strtolower( $parts['scheme'] ), [ 'http', 'https' ], true ) ) {
			return [];
		}

		return $parts;
	}

	/**
	 * Checks that a lowercased host is a bracketed IPv6 address or an ASCII hostname.
	 *
	 * @param string $host Lowercased host.
	 *
	 * @return bool
	 */
	private function is_valid_origin_host( string $host ): bool {
		if ( '[' === $host[0] && ']' === substr( $host, -1 ) ) {
			return false !== filter_var( substr( $host, 1, -1 ), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 );
		}

		return (bool) preg_match( '/^[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?)*$/', $host );
	}

	/**
	 * Checks that the raw authority matches the parsed host and port, and that the port is in range.
	 *
	 * @param string $url    Original URL.
	 * @param string $scheme Lowercased scheme.
	 * @param string $host   Lowercased host.
	 * @param array  $parts  Parsed URL parts.
	 *
	 * @return bool
	 */
	private function is_canonical_authority( string $url, string $scheme, string $host, array $parts ): bool {
		$prefix = $scheme . '://';

		if ( strtolower( substr( $url, 0, strlen( $prefix ) ) ) !== $prefix ) {
			return false;
		}

		$authority = strtolower( substr( $url, strlen( $prefix ) ) );

		if ( '/' === substr( $authority, -1 ) ) {
			$authority = substr( $authority, 0, -1 );
		}

		// parse_url() is lenient with ports (":80abc", ":+80", ":08443", ":"), so the raw authority must match the parsed parts.
		if ( $host . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' ) !== $authority ) {
			return false;
		}

		// parse_url() returns false for ports above 65535, but can return port 0.
		return ! isset( $parts['port'] ) || 0 < $parts['port'];
	}
}
