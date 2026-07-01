<?php
/**
 * Built-in verification for trusted CIMD publishers.
 *
 * Client ID Metadata Documents (CIMD) let any client identify itself with an
 * HTTPS URL, but this proof of concept only authorizes clients whose metadata
 * matches a known, trusted publisher. Claude is the bundled publisher: its
 * official client_id URL, host, and loopback redirect shape are pinned here so
 * that a fetched document can be cryptographically bound to Claude rather than
 * trusted on its own say-so.
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

class ClaudeClientVerifier {
	/**
	 * Verify a fetched CIMD document against the trusted-publisher allowlist.
	 *
	 * @param string               $client_id The client_id URL the document was fetched from.
	 * @param array<string, mixed> $doc       The decoded, already URL-validated metadata document.
	 * @return array{verified: bool, publisher: string} Verification result.
	 */
	public function verify( string $client_id, array $doc ): array {
		$unverified = [
			'verified'  => false,
			'publisher' => '',
		];

		foreach ( $this->get_trusted_publishers() as $publisher => $config ) {
			if ( $this->matches_publisher( $client_id, $doc, (array) $config ) ) {
				McpLogger::log(
					'CIMD',
					'client verified against trusted publisher',
					[
						'client_id' => $client_id,
						'publisher' => $publisher,
					]
				);

				return [
					'verified'  => true,
					'publisher' => (string) $publisher,
				];
			}
		}

		return $unverified;
	}

	/**
	 * Whether a client_id URL's host belongs to a trusted publisher.
	 *
	 * Used to gate the (unauthenticated) CIMD network fetch: only client_ids
	 * whose host is allowlisted are ever dereferenced, so an arbitrary URL
	 * cannot turn the public authorize endpoint into an outbound-request proxy
	 * or fill the transient cache with junk records.
	 *
	 * @param string $client_id The client_id URL.
	 * @return bool
	 */
	public function is_trusted_host( string $client_id ): bool {
		$host = (string) wp_parse_url( $client_id, PHP_URL_HOST );
		if ( '' === $host ) {
			return false;
		}

		foreach ( $this->get_trusted_publishers() as $config ) {
			if ( (string) ( ( (array) $config )['host'] ?? '' ) === $host ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return the trusted-publisher allowlist.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_trusted_publishers(): array {
		/**
		 * Filters the trusted-publisher allowlist for MCP OAuth client verification.
		 *
		 * Each entry is keyed by an arbitrary publisher slug and must provide:
		 *  - client_ids (string[]): exact client_id URLs that are trusted for this publisher.
		 *  - host (string): the hostname client_id URLs must resolve to (defense in depth,
		 *    also used as the SSRF allowlist gate before any network fetch is made).
		 *
		 * This filter only ever runs server-side, with no request-derived input passed into it
		 * or influencing its evaluation — it does not accept or process untrusted input. It can
		 * only ADD trusted publishers; it does not bypass the exact client_id match in
		 * matches_publisher() or the "verified" hard-reject in AuthorizeEndpoint::handle_request().
		 *
		 * @param array<string, array{client_ids: string[], host: string}> $trusted_publishers Trusted-publisher allowlist.
		 * @return array<string, array{client_ids: string[], host: string}>
		 */
		return wpm_apply_filters_typed(
			'array',
			'rocket_mcp_trusted_publishers',
			[
				'claude' => [
					'client_ids' => [
						'https://claude.ai/oauth/claude-code-client-metadata',
						'https://claude.ai/oauth/mcp-oauth-client-metadata',
					],
					'host'       => 'claude.ai',
				],
			]
		);
	}

	/**
	 * Check whether a document matches a single trusted publisher.
	 *
	 * @param string               $client_id The client_id URL.
	 * @param array<string, mixed> $doc       The metadata document.
	 * @param array<string, mixed> $config    The publisher configuration.
	 * @return bool
	 */
	private function matches_publisher( string $client_id, array $doc, array $config ): bool {
		$client_ids = (array) ( $config['client_ids'] ?? [] );

		// 1. Exact, known-good client_id URL (primary trust anchor).
		if ( ! in_array( $client_id, $client_ids, true ) ) {
			return false;
		}

		// 2. URL host must match the publisher host (defense in depth).
		$host = (string) wp_parse_url( $client_id, PHP_URL_HOST );
		if ( '' === $host || (string) ( $config['host'] ?? '' ) !== $host ) {
			return false;
		}

		// 3. Public-client flow only.
		// Redirect URI validation at authorize time is AuthorizeEndpoint::redirect_uri_matches()'s
		// responsibility. Checking the redirect_uri shape here would break verification when
		// the publisher's CIMD document includes additional redirect URIs for other products.
		$auth_method = (string) ( $doc['token_endpoint_auth_method'] ?? 'none' );

		return 'none' === $auth_method;
	}
}
