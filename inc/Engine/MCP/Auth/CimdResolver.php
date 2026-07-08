<?php
/**
 * Client ID Metadata Document (CIMD) resolver.
 *
 * Replaces Dynamic Client Registration (RFC 7591).  Instead of pre-registering
 * a client and minting a client_id/secret, the client presents an HTTPS URL as
 * its client_id; this resolver dereferences that URL, validates the returned
 * metadata document, and produces a normalised client record compatible with
 * the one AuthorizeEndpoint previously read from ClientRegistration::get_client().
 *
 * CIMD is the default client-registration model in the 2025-11-25 MCP
 * specification (IETF OAuth WG, adopted October 2025).
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

class CimdResolver {
	/**
	 * Maximum size (bytes) of a metadata document we will read.
	 */
	const MAX_DOCUMENT_BYTES = 5120;

	/**
	 * HTTP request timeout (seconds) for fetching a document.
	 */
	const FETCH_TIMEOUT = 5;

	/**
	 * Transient key prefix for cached, validated documents.
	 */
	const CACHE_PREFIX = 'mcp_cimd_';

	/**
	 * Grant types this server supports.
	 */
	const SUPPORTED_GRANT_TYPES = [ 'authorization_code', 'refresh_token' ];

	/**
	 * Trusted-publisher verifier.
	 *
	 * @var ClaudeClientVerifier
	 */
	private ClaudeClientVerifier $verifier;

	/**
	 * Constructor.
	 *
	 * @param ClaudeClientVerifier $verifier Trusted-publisher verifier.
	 */
	public function __construct( ClaudeClientVerifier $verifier ) {
		$this->verifier = $verifier;
	}

	/**
	 * Resolve a client_id URL into a normalised client record.
	 *
	 * @param string $client_id The client_id URL presented by the client.
	 * @return array<string, mixed>|null Normalised client record, or null on any failure.
	 */
	public function resolve( string $client_id ): ?array {
		if ( ! $this->is_valid_client_id_url( $client_id ) ) {
			McpLogger::log( 'CIMD', 'rejected: invalid client_id url', [ 'client_id' => $client_id ] );
			return null;
		}

		// Gate the network fetch on the trusted-publisher host allowlist. The
		// authorize endpoint is unauthenticated, so dereferencing an arbitrary
		// client_id URL would let any caller use this server as an outbound
		// fetch proxy and pollute the transient cache. Only allowlisted hosts
		// are ever fetched; exact client_id verification still happens later.
		if ( ! $this->verifier->is_trusted_host( $client_id ) ) {
			McpLogger::log( 'CIMD', 'rejected: client_id host not in trusted-publisher allowlist', [ 'client_id' => $client_id ] );
			return null;
		}

		$cached = $this->cache_get( $client_id );
		if ( null !== $cached ) {
			return $cached;
		}

		$fetched = $this->fetch_document( $client_id );
		if ( null === $fetched ) {
			return null;
		}

		$record = $this->validate_document( $fetched['doc'], $client_id );
		if ( null === $record ) {
			return null;
		}

		$verification        = $this->verifier->verify( $client_id, $fetched['doc'] );
		$record['verified']  = (bool) $verification['verified'];
		$record['publisher'] = (string) $verification['publisher'];

		$this->cache_set( $client_id, $record, $fetched['ttl'] );

		return $record;
	}

	/**
	 * Validate the shape of a client_id URL per the CIMD spec.
	 *
	 * Requires an HTTPS URL with a path and no fragment or userinfo.
	 *
	 * @param string $client_id The client_id URL.
	 * @return bool
	 */
	private function is_valid_client_id_url( string $client_id ): bool {
		if ( '' === $client_id ) {
			return false;
		}

		$parts = wp_parse_url( $client_id );

		if ( ! is_array( $parts ) ) {
			return false;
		}

		if ( 'https' !== ( $parts['scheme'] ?? '' ) ) {
			return false;
		}

		if ( empty( $parts['host'] ) ) {
			return false;
		}

		$path = (string) ( $parts['path'] ?? '' );
		if ( '' === $path || '/' === $path ) {
			return false;
		}

		if ( isset( $parts['fragment'] ) || isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Fetch a metadata document with SSRF guards and a size cap.
	 *
	 * @param string $url The client_id URL.
	 * @return array{doc: array<string, mixed>, ttl: int}|null Decoded document and cache TTL, or null on failure.
	 */
	private function fetch_document( string $url ) {
		$response = wp_safe_remote_get(
			$url,
			[
				'timeout'             => self::FETCH_TIMEOUT,
				'redirection'         => 0,
				'limit_response_size' => self::MAX_DOCUMENT_BYTES,
				'headers'             => [ 'Accept' => 'application/json' ],
			]
		);

		if ( is_wp_error( $response ) ) {
			McpLogger::log(
				'CIMD',
				'rejected: fetch failed',
				[
					'client_id' => $url,
					'error'     => $response->get_error_message(),
				]
			);
			return null;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status ) {
			McpLogger::log(
				'CIMD',
				'rejected: non-200 status',
				[
					'client_id' => $url,
					'status'    => $status,
				]
				);
			return null;
		}

		$body = (string) wp_remote_retrieve_body( $response );
		if ( strlen( $body ) > self::MAX_DOCUMENT_BYTES ) {
			McpLogger::log(
				'CIMD',
				'rejected: document too large',
				[
					'client_id' => $url,
					'bytes'     => strlen( $body ),
				]
				);
			return null;
		}

		$content_type = (string) wp_remote_retrieve_header( $response, 'content-type' );
		if ( '' !== $content_type && false === strpos( $content_type, 'application/json' ) ) {
			McpLogger::log(
				'CIMD',
				'warning: unexpected content-type',
				[
					'client_id'    => $url,
					'content_type' => $content_type,
				],
				true
				);
		}

		$doc = json_decode( $body, true );
		if ( ! is_array( $doc ) || empty( $doc ) ) {
			McpLogger::log( 'CIMD', 'rejected: body is not a JSON object', [ 'client_id' => $url ] );
			return null;
		}

		return [
			'doc' => $doc,
			'ttl' => $this->parse_ttl( (string) wp_remote_retrieve_header( $response, 'cache-control' ) ),
		];
	}

	/**
	 * Validate a metadata document and build a normalized client record.
	 *
	 * @param array<string, mixed> $doc The decoded metadata document.
	 * @param string               $url The client_id URL it was fetched from.
	 * @return array<string, mixed>|null Normalized record, or null on validation failure.
	 */
	private function validate_document( array $doc, string $url ): ?array {
		// The document's client_id MUST exactly equal the document URL.
		$doc_client_id = isset( $doc['client_id'] ) && is_string( $doc['client_id'] ) ? $doc['client_id'] : '';
		if ( '' === $doc_client_id || $url !== $doc_client_id ) {
			McpLogger::log(
				'CIMD',
				'rejected: client_id mismatch',
				[
					'client_id'          => $url,
					'document_client_id' => $doc_client_id,
				]
				);
			return null;
		}

		// Only public clients are supported (no shared secret to authenticate).
		$auth_method = isset( $doc['token_endpoint_auth_method'] ) ? (string) $doc['token_endpoint_auth_method'] : 'none';
		if ( 'none' !== $auth_method ) {
			McpLogger::log(
				'CIMD',
				'rejected: unsupported token_endpoint_auth_method',
				[
					'client_id' => $url,
					'method'    => $auth_method,
				]
				);
			return null;
		}

		// redirect_uris is required.
		$redirect_uris = $doc['redirect_uris'] ?? [];
		if ( ! is_array( $redirect_uris ) || empty( $redirect_uris ) ) {
			McpLogger::log( 'CIMD', 'rejected: missing redirect_uris', [ 'client_id' => $url ] );
			return null;
		}

		$redirect_uris = array_values(
			array_filter(
				array_map( 'esc_url_raw', array_map( 'strval', $redirect_uris ) )
			)
		);

		if ( empty( $redirect_uris ) ) {
			McpLogger::log( 'CIMD', 'rejected: no valid redirect_uris after sanitisation', [ 'client_id' => $url ] );
			return null;
		}

		// grant_types: intersect with what we support; must include authorization_code.
		$requested_grants = isset( $doc['grant_types'] ) && is_array( $doc['grant_types'] )
			? array_map( 'strval', $doc['grant_types'] )
			: self::SUPPORTED_GRANT_TYPES;

		$grant_types = array_values( array_intersect( self::SUPPORTED_GRANT_TYPES, $requested_grants ) );
		if ( ! in_array( 'authorization_code', $grant_types, true ) ) {
			McpLogger::log(
				'CIMD',
				'rejected: authorization_code grant not offered',
				[
					'client_id'   => $url,
					'grant_types' => $requested_grants,
				]
				);
			return null;
		}

		$client_name = isset( $doc['client_name'] ) ? sanitize_text_field( (string) $doc['client_name'] ) : '';
		if ( '' === $client_name ) {
			$client_name = (string) wp_parse_url( $url, PHP_URL_HOST );
		}

		$client_uri = isset( $doc['client_uri'] ) && is_string( $doc['client_uri'] ) ? esc_url_raw( $doc['client_uri'] ) : '';

		return [
			'client_id'                  => $url,
			'client_name'                => $client_name,
			'client_uri'                 => $client_uri,
			'redirect_uris'              => $redirect_uris,
			'grant_types'                => $grant_types,
			'token_endpoint_auth_method' => 'none',
			'source'                     => 'cimd',
			'verified'                   => false,
			'publisher'                  => '',
		];
	}

	/**
	 * Parse a max-age TTL from a Cache-Control header, clamped to a sane range.
	 *
	 * @param string $cache_control The Cache-Control header value.
	 * @return int TTL in seconds.
	 */
	private function parse_ttl( string $cache_control ): int {
		$default = HOUR_IN_SECONDS;

		if ( '' !== $cache_control && preg_match( '/max-age\s*=\s*(\d+)/i', $cache_control, $matches ) ) {
			$default = (int) $matches[1];
		}

		return (int) max( 300, min( $default, DAY_IN_SECONDS ) );
	}

	/**
	 * Build the transient cache key for a client_id URL.
	 *
	 * @param string $url The client_id URL.
	 * @return string
	 */
	private function cache_key( string $url ): string {
		return self::CACHE_PREFIX . md5( $url );
	}

	/**
	 * Retrieve a cached, validated client record.
	 *
	 * @param string $url The client_id URL.
	 * @return array<string, mixed>|null
	 */
	private function cache_get( string $url ): ?array {
		$cached = get_transient( $this->cache_key( $url ) );

		return is_array( $cached ) ? $cached : null;
	}

	/**
	 * Cache a validated client record. Only successful documents are ever cached.
	 *
	 * @param string               $url    The client_id URL.
	 * @param array<string, mixed> $record The normalised record.
	 * @param int                  $ttl    Cache TTL in seconds.
	 * @return void
	 */
	private function cache_set( string $url, array $record, int $ttl ): void {
		set_transient( $this->cache_key( $url ), $record, $ttl );
	}
}
